<?php

namespace Kejubayer\StockMarket\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class CseService
{
    public function getLatest(): array
    {
        return Cache::remember('cse_latest', 300, function () {
            $url = "https://www.cse.com.bd/market/current_price";
            $response = Http::withoutVerifying()->get($url);

            if (!$response->ok()) return [];

            return $this->parseHtml($response->body());
        });
    }

    private function parseHtml(string $html): array
    {
        $data = [];
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $rows = $xpath->query("//table[contains(@id,'dataTable')]//tr");
        foreach ($rows as $i => $row) {
            if ($i === 0) continue; // skip header
            $cells = $xpath->query(".//td", $row);
            if ($cells->length < 10) continue;

            $linkNode = $xpath->query(".//a", $cells->item(1));
            $link = $linkNode->length ? $linkNode->item(0)->getAttribute('href') : null;
            if ($link && !str_starts_with($link, 'http')) {
                $link = 'https://www.cse.com.bd/' . ltrim($link, '/');
            }

            $data[] = [
                'link'   => $link,
                'symbol' => trim($cells->item(1)->textContent),
                'ltp'    => trim($cells->item(2)->textContent),
                'open'   => trim($cells->item(3)->textContent),
                'high'   => trim($cells->item(4)->textContent),
                'low'    => trim($cells->item(5)->textContent),
                'ycp'    => trim($cells->item(6)->textContent),
                'trade'  => trim($cells->item(7)->textContent),
                'value'  => trim($cells->item(8)->textContent),
                'volume' => trim($cells->item(9)->textContent),
            ];
        }

        return $data;
    }
}
