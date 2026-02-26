<?php

use App\Logic\NetifydCatalogRepository;
use Mockery\MockInterface;

use function Pest\Laravel\get;
use function Pest\Laravel\partialMock;

describe('applications catalog endpoint', function () {
    it('returns catalog data from the repository', function () {
        $data = [['id' => 1, 'label' => 'WhatsApp']];

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($data) {
            $mock->allows('applicationsCatalog')->andReturn($data);
        });

        get('/api/netifyd/applications/catalog')
            ->assertOk()
            ->assertJson($data);
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
    it('returns categories data from the repository', function () {
        $data = [['id' => 1, 'tag' => 'messaging', 'label' => 'Messaging']];

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($data) {
            $mock->allows('applicationsCategories')->andReturn($data);
        });

        get('/api/netifyd/applications/categories')
            ->assertOk()
            ->assertJson($data);
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
    it('returns catalog data from the repository', function () {
        $data = [['id' => 96, 'label' => 'TFTP']];

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($data) {
            $mock->allows('protocolsCatalog')->andReturn($data);
        });

        get('/api/netifyd/protocols/catalog')
            ->assertOk()
            ->assertJson($data);
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
    it('returns categories data from the repository', function () {
        $data = [['id' => 4, 'tag' => 'file-server', 'label' => 'File Server']];

        partialMock(NetifydCatalogRepository::class, function (MockInterface $mock) use ($data) {
            $mock->allows('protocolsCategories')->andReturn($data);
        });

        get('/api/netifyd/protocols/categories')
            ->assertOk()
            ->assertJson($data);
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
