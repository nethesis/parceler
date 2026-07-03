<?php

use App\Logic\NetifydCatalogRepository;
use Illuminate\Support\Facades\Config;
use Mockery\MockInterface;

use function Pest\Laravel\get;
use function Pest\Laravel\partialMock;

it('returns 503 with a Retry-After header once the netifyd rate limit is exceeded', function () {
    Config::set('netifyd.rate-limit', 2);

    partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) {
        $mock->allows('applicationsCatalog')->andReturn([]);
    });

    get('/api/netifyd/applications/catalog')->assertOk();
    get('/api/netifyd/applications/catalog')->assertOk();

    get('/api/netifyd/applications/catalog')
        ->assertServiceUnavailable()
        ->assertHeader('Retry-After');
});

it('returns a randomized Retry-After header between 300 and 900 seconds', function () {
    Config::set('netifyd.rate-limit', 1);

    partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) {
        $mock->allows('applicationsCatalog')->andReturn([]);
    });

    get('/api/netifyd/applications/catalog')->assertOk();

    $response = get('/api/netifyd/applications/catalog')->assertServiceUnavailable();

    expect((int) $response->headers->get('Retry-After'))
        ->toBeGreaterThanOrEqual(300)
        ->toBeLessThanOrEqual(900);
});

it('rate limits globally across clients, not per ip', function () {
    Config::set('netifyd.rate-limit', 1);

    partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) {
        $mock->allows('applicationsCatalog')->andReturn([]);
    });

    get('/api/netifyd/applications/catalog', ['X-Forwarded-For' => '203.0.113.10'])->assertOk();
    get('/api/netifyd/applications/catalog', ['X-Forwarded-For' => '198.51.100.20'])
        ->assertServiceUnavailable();
});
