<?php

namespace App\Logic;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class NetifydCatalogRepository
{
    private const int CACHE_TTL_SECONDS = 43200;

    public function __construct(private string $endpoint, private string $apiKey) {}

    /**
     * @throws Exception
     */
    public function applicationsCatalog(): string
    {
        return $this->fetch('applications/catalog', 'netifyd-applications-catalog', 'netifyd/applications-catalog.json');
    }

    /**
     * @throws Exception
     */
    public function applicationsCategories(): string
    {
        return $this->fetch('applications/categories', 'netifyd-applications-categories', 'netifyd/applications-categories.json');
    }

    /**
     * @throws Exception
     */
    public function protocolsCatalog(): string
    {
        return $this->fetch('protocols/catalog', 'netifyd-protocols-catalog', 'netifyd/protocols-catalog.json');
    }

    /**
     * @throws Exception
     */
    public function protocolsCategories(): string
    {
        return $this->fetch('protocols/categories', 'netifyd-protocols-categories', 'netifyd/protocols-categories.json');
    }

    /**
     * @throws Exception
     */
    private function fetch(string $path, string $cacheKey, string $storagePath): string
    {
        if (Cache::has($cacheKey)) {
            return $storagePath;
        }

        try {
            $data = Http::withHeader('x-api-key', $this->apiKey)
                ->get($this->endpoint.'/api/v2/integrator/'.$path, ['version' => '5.1'])
                ->throw()
                ->json('data');
        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not fetch '.$path.' from netifyd: '.$e->getMessage());
        }

        Storage::makeDirectory(dirname($storagePath));
        Storage::put($storagePath, json_encode($data));
        Cache::put($cacheKey, true, now()->addSeconds(self::CACHE_TTL_SECONDS));

        return $storagePath;
    }

    private function isFresh(string $cacheKey, string $storagePath): bool
    {
        if (! Storage::exists($storagePath)) {
            return false;
        }

        return Cache::has($cacheKey);
    }
}
