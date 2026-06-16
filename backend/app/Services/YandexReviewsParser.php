<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class YandexReviewsParser
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'headers' => [
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
            ],
        ]);
    }

    public function parse(string $url): array
    {
        $response = $this->client->get($url, ['allow_redirects' => true, 'http_errors' => false]);
        $html     = (string) $response->getBody();
        file_put_contents(storage_path('logs/yandex_page.html'), $html);
        $embeddedData = $this->extractEmbeddedJson($html);
        $businessId   = $this->extractBusinessId($html);
        $name         = $this->extractName($html, $embeddedData);
        $rating       = $this->extractRating($html, $embeddedData);
        $totalRatings = $this->extractTotalRatings($html, $embeddedData);
        $address      = $this->extractAddress($html, $embeddedData);
        $reviews      = [];

        try {
            $reviews = $this->fetchAllReviews($html, $url);
        } catch (\Throwable $e) {
            Log::warning('Playwright failed: ' . $e->getMessage());

            // no fallback
            $reviews = [];
        }

        return [
            'business_id'    => $businessId,
            'name'           => $name,
            'url'            => $url,
            'average_rating' => $rating,
            'total_ratings'  => $totalRatings,
            'total_reviews'  => count($reviews),
            'address'        => $address,
            'reviews'        => $reviews ?? [],
        ];
    }
    private function extractEmbeddedJson(string $html): array
    {
        $data = [];
        if (preg_match('/"ratingData"\s*:\s*\{[^}]*"ratingCount"\s*:\s*(\d+)[^}]*"ratingValue"\s*:\s*([\d.]+)[^}]*"reviewCount"\s*:\s*(\d+)[^}]*\}/s', $html, $m)) {
            $data['ratingCount'] = (int) $m[1];
            $data['ratingValue'] = (float) $m[2];
            $data['reviewCount'] = (int) $m[3];
        }
        if (preg_match('/"fullAddress"\s*:\s*"([^"]+)"/', $html, $m)) {
            $data['fullAddress'] = $m[1];
        }

        if (preg_match('/"shortTitle"\s*:\s*"([^"]+)"/', $html, $m)) {
            $data['shortTitle'] = $m[1];
        }

        if (preg_match('/data-chunk="reviews".*?card-section-header__title[^>]*>\s*(\d+)\s*reviews/s', $html, $m)) {
            $data['htmlReviewCount'] = (int) $m[1];
        }

        return $data;
    }
    private function extractBusinessId(string $html): ?string
    {
        if (preg_match('/class="[^"]*business-card-view[^"]*"[^>]*data-id="(\d+)"/s', $html, $m)) {
            return $m[1];
        }

        if (preg_match('/"businessId"\s*:\s*"?(\d+)"?/i', $html, $m)) {
            return $m[1];
        }

        return null;
    }
    private function extractName(string $html, array $embeddedData): string
    {
        if (preg_match('/class="[^"]*card-title-view__title-link[^"]*"[^>]*>\s*([^<]+)\s*</s', $html, $m)) {
            return trim($m[1]);
        }

        if (preg_match('/class="[^"]*card-title-view__title[^"]*"[^>]*>.*?<span[^>]*>\s*([^<]+)\s*<\/span>/s', $html, $m)) {
            return trim($m[1]);
        }

        if (! empty($embeddedData['shortTitle'])) {
            return $embeddedData['shortTitle'];
        }

        return 'Unknown';
    }
    private function extractRating(string $html, array $embeddedData): ?float
    {
        if (preg_match('/class="[^"]*business-rating-badge-view__rating-text[^"]*"[^>]*>\s*([\d.]+)\s*</s', $html, $m)) {
            return (float) $m[1];
        }

        if (isset($embeddedData['ratingValue'])) {
            return round($embeddedData['ratingValue'], 1);
        }

        return null;
    }
    private function extractTotalRatings(string $html, array $embeddedData): int
    {
        if (preg_match('/class="[^"]*business-header-rating-view__text[^"]*"[^>]*>\s*([\d\s,]+)\s*ratings?\s*</s', $html, $m)) {
            return (int) str_replace([',', ' '], '', $m[1]);
        }

        if (isset($embeddedData['ratingCount'])) {
            return $embeddedData['ratingCount'];
        }

        return 0;
    }
    private function extractAddress(string $html, array $embeddedData): string
    {
        if (preg_match('/class="[^"]*business-contacts-view__address[^"]*"[^>]*>(.*?)<\/div>/s', $html, $m)) {
            $addr = trim(preg_replace('/\s+/', ' ', trim(strip_tags($m[1]))));
            if (! empty($addr)) {
                return $addr;
            }

        }
        if (! empty($embeddedData['fullAddress'])) {
            return $embeddedData['fullAddress'];
        }

        return '';
    }
    private function fetchAllReviews(string $html, string $url): array
    {
        $reviewsPath = $this->extractReviewsPath($html);

        $reviewsUrl =  'https://yandex.com' . $reviewsPath;
        Log::warning('reviewsPath=' . $reviewsPath);
        Log::warning('reviewsUrl=' . $reviewsUrl);
        $process = new Process([
            'node',
            base_path('parser/yandex-parser2.js'),
            $reviewsUrl,
        ]);

        $process->setTimeout(60000);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput());
        }

        $output = $process->getOutput();

        $data = json_decode($output, true);
        // Log::warning('data: ' . $output);
        $allReviews = $data['reviews'] ?? [];

        return $allReviews;
    }
    private function extractReviewsPath(string $html): ?string
    {
        if (preg_match(
            '/tabs-select-view__label[^>]*href="([^"]+reviews\/)"/',
            $html,
            $m
        )) {
            Log::warning('$m[1]: ' . $m[1]);
            return $m[1];
        }

        return null;
    }
}
