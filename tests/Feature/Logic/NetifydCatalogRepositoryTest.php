<?php

use App\Logic\NetifydCatalogRepository;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->repository = new NetifydCatalogRepository('http://127.0.0.1', 'api-key');
});

describe('applications catalog', function () {
    it('returns cached data without making an http request', function () {
        $cached = ['data' => [['id' => 1, 'label' => 'WhatsApp']]];
        Cache::shouldReceive('get')->with('netifyd-applications-catalog')->andReturn($cached['data']);

        Http::fake();

        expect($this->repository->applicationsCatalog())->toBe($cached['data']);

        Http::assertNothingSent();
    });

    it('fetches from netifyd and caches for 12 hours on cache miss', function () {
        $data = ['data' => [['id' => 1, 'label' => 'WhatsApp']]];
        Cache::spy();

        Http::fake(['*' => Http::response(json_encode($data))]);

        expect($this->repository->applicationsCatalog())->toBe($data['data']);

        Cache::shouldHaveReceived('get')->with('netifyd-applications-catalog')->once();
        Cache::shouldHaveReceived('put')->with('netifyd-applications-catalog', $data['data'], \Mockery::type(\Carbon\Carbon::class))->once();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-api-key', 'api-key')
                && str_contains($request->url(), '/api/v2/integrator/applications/catalog')
                && $request->data()['version'] === '5.1';
        });
    });

    it('throws an exception on server error', function () {
        Cache::spy();
        Http::fake(['*' => Http::response([], 500)]);

        $this->repository->applicationsCatalog();
    })->throws('Could not fetch applications/catalog from netifyd:');
});

describe('applications categories', function () {
    it('returns cached data without making an http request', function () {
        $cached = ['data' => [['id' => 1, 'tag' => 'messaging', 'label' => 'Messaging']]];
        Cache::shouldReceive('get')->with('netifyd-applications-categories')->andReturn($cached['data']);

        Http::fake();

        expect($this->repository->applicationsCategories())->toBe($cached['data']);

        Http::assertNothingSent();
    });

    it('fetches from netifyd and caches for 12 hours on cache miss', function () {
        $data = ['data' => [['id' => 1, 'tag' => 'messaging', 'label' => 'Messaging']]];
        Cache::spy();

        Http::fake(['*' => Http::response(json_encode($data))]);

        expect($this->repository->applicationsCategories())->toBe($data['data']);

        Cache::shouldHaveReceived('get')->with('netifyd-applications-categories')->once();
        Cache::shouldHaveReceived('put')->with('netifyd-applications-categories', $data['data'], \Mockery::type(\Carbon\Carbon::class))->once();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-api-key', 'api-key')
                && str_contains($request->url(), '/api/v2/integrator/applications/categories')
                && $request->data()['version'] === '5.1';
        });
    });

    it('throws an exception on server error', function () {
        Cache::spy();
        Http::fake(['*' => Http::response([], 500)]);

        $this->repository->applicationsCategories();
    })->throws('Could not fetch applications/categories from netifyd:');
});

describe('protocols catalog', function () {
    it('returns cached data without making an http request', function () {
        $cached = ['data' => [['id' => 96, 'label' => 'TFTP']]];
        Cache::shouldReceive('get')->with('netifyd-protocols-catalog')->andReturn($cached['data']);

        Http::fake();

        expect($this->repository->protocolsCatalog())->toBe($cached['data']);

        Http::assertNothingSent();
    });

    it('fetches from netifyd and caches for 12 hours on cache miss', function () {
        $data = ['data' => [['id' => 96, 'label' => 'TFTP']]];
        Cache::spy();

        Http::fake(['*' => Http::response(json_encode($data))]);

        expect($this->repository->protocolsCatalog())->toBe($data['data']);

        Cache::shouldHaveReceived('get')->with('netifyd-protocols-catalog')->once();
        Cache::shouldHaveReceived('put')->with('netifyd-protocols-catalog', $data['data'], \Mockery::type(\Carbon\Carbon::class))->once();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-api-key', 'api-key')
                && str_contains($request->url(), '/api/v2/integrator/protocols/catalog')
                && $request->data()['version'] === '5.1';
        });
    });

    it('throws an exception on server error', function () {
        Cache::spy();
        Http::fake(['*' => Http::response([], 500)]);

        $this->repository->protocolsCatalog();
    })->throws('Could not fetch protocols/catalog from netifyd:');
});

describe('protocols categories', function () {
    it('returns cached data without making an http request', function () {
        $cached = ['data' => [['id' => 4, 'tag' => 'file-server', 'label' => 'File Server']]];
        Cache::shouldReceive('get')->with('netifyd-protocols-categories')->andReturn($cached['data']);

        Http::fake();

        expect($this->repository->protocolsCategories())->toBe($cached['data']);

        Http::assertNothingSent();
    });

    it('fetches from netifyd and caches for 12 hours on cache miss', function () {
        $data = ['data' => [['id' => 4, 'tag' => 'file-server', 'label' => 'File Server']]];
        Cache::spy();

        Http::fake(['*' => Http::response(json_encode($data))]);

        expect($this->repository->protocolsCategories())->toBe($data['data']);

        Cache::shouldHaveReceived('get')->with('netifyd-protocols-categories')->once();
        Cache::shouldHaveReceived('put')->with('netifyd-protocols-categories', $data['data'], \Mockery::type(\Carbon\Carbon::class))->once();

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('x-api-key', 'api-key')
                && str_contains($request->url(), '/api/v2/integrator/protocols/categories')
                && $request->data()['version'] === '5.1';
        });
    });

    it('throws an exception on server error', function () {
        Cache::spy();
        Http::fake(['*' => Http::response([], 500)]);

        $this->repository->protocolsCategories();
    })->throws('Could not fetch protocols/categories from netifyd:');
});
