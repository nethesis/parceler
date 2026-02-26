<?php

namespace App\Logic;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class NetifydCatalogRepository
{
    public function __construct(private string $endpoint, private string $apiKey) {}

    /**
     * @return array<int, mixed>
     *
     * @throws Exception
     */
    public function applicationsCatalog(): array
    {
        return $this->fetch('applications/catalog', 'netifyd-applications-catalog');
    }

    /**
     * @return array<int, mixed>
     *
     * @throws Exception
     */
    public function applicationsCategories(): array
    {
        return $this->fetch('applications/categories', 'netifyd-applications-categories');
    }

    /**
     * @return array<int, mixed>
     *
     * @throws Exception
     */
    public function protocolsCatalog(): array
    {
        return $this->fetch('protocols/catalog', 'netifyd-protocols-catalog');
    }

    /**
     * @return array<int, mixed>
     *
     * @throws Exception
     */
    public function protocolsCategories(): array
    {
        return $this->fetch('protocols/categories', 'netifyd-protocols-categories');
    }

    /**
     * @return array<int, mixed>
     *
     * @throws Exception
     */
    private function fetch(string $path, string $cacheKey): array
    {
        $cached = Cache::get($cacheKey);
        if ($cached != null) {
            return $cached;
        }

        try {
            $data = Http::withHeader('x-api-key', $this->apiKey)
                ->get($this->endpoint.'/api/v2/integrator/'.$path, ['version' => '5.1'])
                ->throw()
                ->json('data');
        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not fetch '.$path.' from netifyd: '.$e->getMessage());
        }

        Cache::put($cacheKey, $data, now()->addHours(12));

        return $data;
    }
}
