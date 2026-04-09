<?php

use App\Logic\NetifydCatalogRepository;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function netifydCatalogRepository(): NetifydCatalogRepository
{
    return new NetifydCatalogRepository('http://127.0.0.1', 'api-key');
}

describe('applications catalog', function () {
    it('returns the stored path when the file is fresh', function () {
        Storage::fake();
        Storage::put('netifyd/applications-catalog.json', json_encode([['id' => 1, 'label' => 'WhatsApp']]));
        Cache::shouldReceive('has')->with('netifyd-applications-catalog')->andReturnTrue();

        Http::fake();

        expect(netifydCatalogRepository()->applicationsCatalog())->toBe('netifyd/applications-catalog.json');

        Http::assertNothingSent();
    });

    it('fetches from netifyd and stores the json when the file is missing', function () {
        Storage::fake();
        Cache::shouldReceive('has')->with('netifyd-applications-catalog')->andReturnFalse();
        Cache::shouldReceive('put')->with('netifyd-applications-catalog', true, Mockery::any())->once();

        Http::fake(['*' => Http::response(['data' => [['id' => 1, 'label' => 'WhatsApp']]])]);

        expect(netifydCatalogRepository()->applicationsCatalog())->toBe('netifyd/applications-catalog.json');

        Storage::assertExists('netifyd/applications-catalog.json');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-api-key', 'api-key')
                && str_contains($request->url(), '/api/v2/integrator/applications/catalog')
                && $request->data()['version'] === '5.1';
        });
    });

    it('replaces stale json on refresh', function () {
        Storage::fake();
        Storage::put('netifyd/applications-catalog.json', json_encode([['id' => 1, 'label' => 'Old']]));
        Cache::shouldReceive('has')->with('netifyd-applications-catalog')->andReturnFalse();
        Cache::shouldReceive('put')->with('netifyd-applications-catalog', true, Mockery::any())->once();

        Http::fake(['*' => Http::response(['data' => [['id' => 1, 'label' => 'New']]])]);

        expect(netifydCatalogRepository()->applicationsCatalog())->toBe('netifyd/applications-catalog.json');

        Storage::assertExists('netifyd/applications-catalog.json');
    });

    it('throws an exception on server error', function () {
        Storage::fake();
        Cache::shouldReceive('has')->with('netifyd-applications-catalog')->andReturnFalse();
        Http::fake(['*' => Http::response([], 500)]);

        netifydCatalogRepository()->applicationsCatalog();
    })->throws('Could not fetch applications/catalog from netifyd:');
});

describe('applications categories', function () {
    it('returns the stored path when the file is fresh', function () {
        Storage::fake();
        Storage::put('netifyd/applications-categories.json', json_encode([['id' => 1, 'tag' => 'messaging', 'label' => 'Messaging']]));
        Cache::shouldReceive('has')->with('netifyd-applications-categories')->andReturnTrue();

        Http::fake();

        expect(netifydCatalogRepository()->applicationsCategories())->toBe('netifyd/applications-categories.json');

        Http::assertNothingSent();
    });

    it('fetches from netifyd and stores the json when the file is missing', function () {
        Storage::fake();
        Cache::shouldReceive('has')->with('netifyd-applications-categories')->andReturnFalse();
        Cache::shouldReceive('put')->with('netifyd-applications-categories', true, Mockery::any())->once();

        Http::fake(['*' => Http::response(['data' => [['id' => 1, 'tag' => 'messaging', 'label' => 'Messaging']]])]);

        expect(netifydCatalogRepository()->applicationsCategories())->toBe('netifyd/applications-categories.json');

        Storage::assertExists('netifyd/applications-categories.json');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-api-key', 'api-key')
                && str_contains($request->url(), '/api/v2/integrator/applications/categories')
                && $request->data()['version'] === '5.1';
        });
    });

    it('replaces stale json on refresh', function () {
        Storage::fake();
        Storage::put('netifyd/applications-categories.json', json_encode([['id' => 1, 'tag' => 'messaging', 'label' => 'Old']]));
        Cache::shouldReceive('has')->with('netifyd-applications-categories')->andReturnFalse();
        Cache::shouldReceive('put')->with('netifyd-applications-categories', true, Mockery::any())->once();

        Http::fake(['*' => Http::response(['data' => [['id' => 1, 'tag' => 'messaging', 'label' => 'New']]])]);

        expect(netifydCatalogRepository()->applicationsCategories())->toBe('netifyd/applications-categories.json');

        Storage::assertExists('netifyd/applications-categories.json');
    });

    it('throws an exception on server error', function () {
        Storage::fake();
        Cache::shouldReceive('has')->with('netifyd-applications-categories')->andReturnFalse();
        Http::fake(['*' => Http::response([], 500)]);

        netifydCatalogRepository()->applicationsCategories();
    })->throws('Could not fetch applications/categories from netifyd:');
});

describe('protocols catalog', function () {
    it('returns the stored path when the file is fresh', function () {
        Storage::fake();
        Storage::put('netifyd/protocols-catalog.json', json_encode([['id' => 96, 'label' => 'TFTP']]));
        Cache::shouldReceive('has')->with('netifyd-protocols-catalog')->andReturnTrue();

        Http::fake();

        expect(netifydCatalogRepository()->protocolsCatalog())->toBe('netifyd/protocols-catalog.json');

        Http::assertNothingSent();
    });

    it('fetches from netifyd and stores the json when the file is missing', function () {
        Storage::fake();
        Cache::shouldReceive('has')->with('netifyd-protocols-catalog')->andReturnFalse();
        Cache::shouldReceive('put')->with('netifyd-protocols-catalog', true, Mockery::any())->once();

        Http::fake(['*' => Http::response(['data' => [['id' => 96, 'label' => 'TFTP']]])]);

        expect(netifydCatalogRepository()->protocolsCatalog())->toBe('netifyd/protocols-catalog.json');

        Storage::assertExists('netifyd/protocols-catalog.json');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-api-key', 'api-key')
                && str_contains($request->url(), '/api/v2/integrator/protocols/catalog')
                && $request->data()['version'] === '5.1';
        });
    });

    it('replaces stale json on refresh', function () {
        Storage::fake();
        Storage::put('netifyd/protocols-catalog.json', json_encode([['id' => 96, 'label' => 'Old']]));
        Cache::shouldReceive('has')->with('netifyd-protocols-catalog')->andReturnFalse();
        Cache::shouldReceive('put')->with('netifyd-protocols-catalog', true, Mockery::any())->once();

        Http::fake(['*' => Http::response(['data' => [['id' => 96, 'label' => 'New']]])]);

        expect(netifydCatalogRepository()->protocolsCatalog())->toBe('netifyd/protocols-catalog.json');

        Storage::assertExists('netifyd/protocols-catalog.json');
    });

    it('throws an exception on server error', function () {
        Storage::fake();
        Cache::shouldReceive('has')->with('netifyd-protocols-catalog')->andReturnFalse();
        Http::fake(['*' => Http::response([], 500)]);

        netifydCatalogRepository()->protocolsCatalog();
    })->throws('Could not fetch protocols/catalog from netifyd:');
});

describe('protocols categories', function () {
    it('returns the stored path when the file is fresh', function () {
        Storage::fake();
        Storage::put('netifyd/protocols-categories.json', json_encode([['id' => 4, 'tag' => 'file-server', 'label' => 'File Server']]));
        Cache::shouldReceive('has')->with('netifyd-protocols-categories')->andReturnTrue();

        Http::fake();

        expect(netifydCatalogRepository()->protocolsCategories())->toBe('netifyd/protocols-categories.json');

        Http::assertNothingSent();
    });

    it('fetches from netifyd and stores the json when the file is missing', function () {
        Storage::fake();
        Cache::shouldReceive('has')->with('netifyd-protocols-categories')->andReturnFalse();
        Cache::shouldReceive('put')->with('netifyd-protocols-categories', true, Mockery::any())->once();

        Http::fake(['*' => Http::response(['data' => [['id' => 4, 'tag' => 'file-server', 'label' => 'File Server']]])]);

        expect(netifydCatalogRepository()->protocolsCategories())->toBe('netifyd/protocols-categories.json');

        Storage::assertExists('netifyd/protocols-categories.json');

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-api-key', 'api-key')
                && str_contains($request->url(), '/api/v2/integrator/protocols/categories')
                && $request->data()['version'] === '5.1';
        });
    });

    it('replaces stale json on refresh', function () {
        Storage::fake();
        Storage::put('netifyd/protocols-categories.json', json_encode([['id' => 4, 'tag' => 'file-server', 'label' => 'Old']]));
        Cache::shouldReceive('has')->with('netifyd-protocols-categories')->andReturnFalse();
        Cache::shouldReceive('put')->with('netifyd-protocols-categories', true, Mockery::any())->once();

        Http::fake(['*' => Http::response(['data' => [['id' => 4, 'tag' => 'file-server', 'label' => 'New']]])]);

        expect(netifydCatalogRepository()->protocolsCategories())->toBe('netifyd/protocols-categories.json');

        Storage::assertExists('netifyd/protocols-categories.json');
    });

    it('throws an exception on server error', function () {
        Storage::fake();
        Cache::shouldReceive('has')->with('netifyd-protocols-categories')->andReturnFalse();
        Http::fake(['*' => Http::response([], 500)]);

        netifydCatalogRepository()->protocolsCategories();
    })->throws('Could not fetch protocols/categories from netifyd:');
});
