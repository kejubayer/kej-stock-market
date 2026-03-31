<?php

namespace Kejubayer\StockMarket\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DseService
{
    public function getLatest(): array
    {
        return Cache::remember('dse_latest', 300, function () {
            $url = "https://www.dsebd.org/latest_share_price_scroll_l.php";
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

        $rows = $xpath->query("//table[contains(@class,'table')]//tr");
        foreach ($rows as $i => $row) {
            if ($i === 0) continue; // skip header
            $cells = $xpath->query(".//td", $row);
            if ($cells->length < 10) continue;

            $data[] = [
                'symbol' => trim($cells->item(0)->textContent),
                'ltp'    => trim($cells->item(1)->textContent),
                'high'   => trim($cells->item(2)->textContent),
                'low'    => trim($cells->item(3)->textContent),
                'closep' => trim($cells->item(4)->textContent),
                'ycp'    => trim($cells->item(5)->textContent),
                'change' => trim($cells->item(6)->textContent),
                'trade'  => trim($cells->item(7)->textContent),
                'value'  => trim($cells->item(8)->textContent),
                'volume' => trim($cells->item(9)->textContent),
            ];
        }

        return $data;
    }
}
