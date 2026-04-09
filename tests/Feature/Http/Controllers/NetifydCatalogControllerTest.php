<?php

use App\Logic\NetifydCatalogRepository;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

use function Pest\Laravel\get;
use function Pest\Laravel\partialMock;

describe('applications catalog endpoint', function () {
    it('downloads the stored file when temporary urls are unavailable', function () {
        $path = 'netifyd/applications-catalog.json';

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($path) {
            $mock->allows('applicationsCatalog')->andReturn($path);
        });

        Storage::shouldReceive('providesTemporaryUrls')->andReturnFalse();
        Storage::shouldReceive('download')->with($path)->andReturn(response('', 200));

        get('/api/netifyd/applications/catalog')
            ->assertOk();
    });

    it('redirects to a temporary url when supported', function () {
        $path = 'netifyd/applications-catalog.json';

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($path) {
            $mock->allows('applicationsCatalog')->andReturn($path);
        });

        Storage::shouldReceive('providesTemporaryUrls')->andReturnTrue();
        Storage::shouldReceive('temporaryUrl')->with($path, Mockery::type(DateTimeInterface::class))->andReturn('https://example.test/temp');

        get('/api/netifyd/applications/catalog')
            ->assertRedirect('https://example.test/temp');
    });

    it('returns 500 when the repository throws', function () {
        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) {
            $mock->allows('applicationsCatalog')->andThrow(new Exception('Could not fetch applications/catalog from netifyd: connection error'));
        });

        get('/api/netifyd/applications/catalog')
            ->assertInternalServerError()
            ->assertJson(['message' => 'Could not fetch applications/catalog from netifyd: connection error']);
    });
});

describe('applications categories endpoint', function () {
    it('downloads the stored file when temporary urls are unavailable', function () {
        $path = 'netifyd/applications-categories.json';

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($path) {
            $mock->allows('applicationsCategories')->andReturn($path);
        });

        Storage::shouldReceive('providesTemporaryUrls')->andReturnFalse();
        Storage::shouldReceive('download')->with($path)->andReturn(response('', 200));

        get('/api/netifyd/applications/categories')
            ->assertOk();
    });

    it('redirects to a temporary url when supported', function () {
        $path = 'netifyd/applications-categories.json';

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($path) {
            $mock->allows('applicationsCategories')->andReturn($path);
        });

        Storage::shouldReceive('providesTemporaryUrls')->andReturnTrue();
        Storage::shouldReceive('temporaryUrl')->with($path, Mockery::type(DateTimeInterface::class))->andReturn('https://example.test/temp');

        get('/api/netifyd/applications/categories')
            ->assertRedirect('https://example.test/temp');
    });

    it('returns 500 when the repository throws', function () {
        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) {
            $mock->allows('applicationsCategories')->andThrow(new Exception('Could not fetch applications/categories from netifyd: connection error'));
        });

        get('/api/netifyd/applications/categories')
            ->assertInternalServerError()
            ->assertJson(['message' => 'Could not fetch applications/categories from netifyd: connection error']);
    });
});

describe('protocols catalog endpoint', function () {
    it('downloads the stored file when temporary urls are unavailable', function () {
        $path = 'netifyd/protocols-catalog.json';

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($path) {
            $mock->allows('protocolsCatalog')->andReturn($path);
        });

        Storage::shouldReceive('providesTemporaryUrls')->andReturnFalse();
        Storage::shouldReceive('download')->with($path)->andReturn(response('', 200));

        get('/api/netifyd/protocols/catalog')
            ->assertOk();
    });

    it('redirects to a temporary url when supported', function () {
        $path = 'netifyd/protocols-catalog.json';

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($path) {
            $mock->allows('protocolsCatalog')->andReturn($path);
        });

        Storage::shouldReceive('providesTemporaryUrls')->andReturnTrue();
        Storage::shouldReceive('temporaryUrl')->with($path, Mockery::type(DateTimeInterface::class))->andReturn('https://example.test/temp');

        get('/api/netifyd/protocols/catalog')
            ->assertRedirect('https://example.test/temp');
    });

    it('returns 500 when the repository throws', function () {
        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) {
            $mock->allows('protocolsCatalog')->andThrow(new Exception('Could not fetch protocols/catalog from netifyd: connection error'));
        });

        get('/api/netifyd/protocols/catalog')
            ->assertInternalServerError()
            ->assertJson(['message' => 'Could not fetch protocols/catalog from netifyd: connection error']);
    });
});

describe('protocols categories endpoint', function () {
    it('downloads the stored file when temporary urls are unavailable', function () {
        $path = 'netifyd/protocols-categories.json';

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($path) {
            $mock->allows('protocolsCategories')->andReturn($path);
        });

        Storage::shouldReceive('providesTemporaryUrls')->andReturnFalse();
        Storage::shouldReceive('download')->with($path)->andReturn(response('', 200));

        get('/api/netifyd/protocols/categories')
            ->assertOk();
    });

    it('redirects to a temporary url when supported', function () {
        $path = 'netifyd/protocols-categories.json';

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($path) {
            $mock->allows('protocolsCategories')->andReturn($path);
        });

        Storage::shouldReceive('providesTemporaryUrls')->andReturnTrue();
        Storage::shouldReceive('temporaryUrl')->with($path, Mockery::type(DateTimeInterface::class))->andReturn('https://example.test/temp');

        get('/api/netifyd/protocols/categories')
            ->assertRedirect('https://example.test/temp');
    });

    it('returns 500 when the repository throws', function () {
        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) {
            $mock->allows('protocolsCategories')->andThrow(new Exception('Could not fetch protocols/categories from netifyd: connection error'));
        });

        get('/api/netifyd/protocols/categories')
            ->assertInternalServerError()
            ->assertJson(['message' => 'Could not fetch protocols/categories from netifyd: connection error']);
    });
});
