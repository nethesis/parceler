<?php

use App\Logic\LicenceVerification;
use App\Logic\NetifydLicenceRepository;
use App\NetifydLicenceType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;

use function Pest\Laravel\get;
use function Pest\Laravel\partialMock;
use function Pest\Laravel\withBasicAuth;

describe('middleware checking', function () {
    it('cannot access enterprise licence without credentials', function (string $url) {
        get($url)
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Basic');
    })->with([
        '/api/netifyd/enterprise/licence',
        '/api/netifyd/community/licence',
    ]);

    it('can access free licence without credentials', function () {
        Cache::expects('has')->with(NetifydLicenceType::COMMUNITY->cacheLabel())->andReturnTrue();
        Cache::expects('get')->with(NetifydLicenceType::COMMUNITY->cacheLabel())->andReturn(['license_key' => 'cache']);
        get('/api/netifyd/licence')
            ->assertOk()
            ->assertJson(['license_key' => 'cache']);
    });
});

describe('controller testing', function () {
    beforeEach(function () {
        partialMock(LicenceVerification::class, function (MockInterface $mock) {
            $mock->allows([
                'enterpriseCheck' => true,
                'communityCheck' => true,
            ]);
        });
    });

    it('can access enterprise licence with credentials', function () {
        Cache::expects('has')->with(NetifydLicenceType::ENTERPRISE->cacheLabel())->andReturnTrue();
        Cache::expects('get')->with(NetifydLicenceType::ENTERPRISE->cacheLabel())->andReturn(['license_key' => 'cache']);
        withBasicAuth('', '')
            ->get('/api/netifyd/community/licence')
            ->assertOk()
            ->assertJson(['license_key' => 'cache']);
    });

    it('serves correctly cache if present', function () {
        Cache::expects('has')->with(NetifydLicenceType::ENTERPRISE->cacheLabel())->andReturnTrue();
        Cache::expects('get')->with(NetifydLicenceType::ENTERPRISE->cacheLabel())->andReturn(['license_key' => 'cached-license-key']);
        Http::preventStrayRequests();
        Http::fake();
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/licence')
            ->assertOk()
            ->assertJson([
                'license_key' => 'cached-license-key',
            ]);
    });

    it('handles errors from netifyd server', function () {
        partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) {
            $mock->expects('listLicences')
                ->andThrow(new Exception('Netifyd server error'));
        });
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/licence')
            ->assertInternalServerError()
            ->assertJson([
                'message' => 'Netifyd server error',
            ]);
    });

    it('list licences', function () {
        $expiration = now()->addDays(2);
        $creation = now()->subDay();
        $licence = [
            'issued_to' => NetifydLicenceType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => $expiration->unix(),
            ],
            'created_at' => [
                'unix' => $creation->unix(),
            ],
        ];
        partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) use ($licence) {
            $mock->expects('listLicences')
                ->andReturn([$licence]);
        });
        Cache::expects('has')->with(NetifydLicenceType::ENTERPRISE->cacheLabel())->andReturnFalse();
        Cache::expects('put')->with(NetifydLicenceType::ENTERPRISE->cacheLabel(), $licence, ($expiration->unix() - $creation->unix()) / 2);
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/licence')
            ->assertOk()
            ->json($licence);
    });

    it('licence not found', function () {
        partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) {
            $mock->expects('listLicences')
                ->andReturn([]);
            $mock->expects('createLicence')
                ->with(NetifydLicenceType::ENTERPRISE)
                ->andreturn([]);
        });
        withBasicAuth('system-id', 'secret')->get('/api/netifyd/community/licence');
    });

    it('cannot create new licence', function () {
        partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) {
            $mock->expects('listLicences')
                ->andReturn([]);
            $mock->expects('createLicence')
                ->with(NetifydLicenceType::ENTERPRISE)
                ->andThrow(new Exception('Cannot create licence'));
        });
        withBasicAuth('', '')
            ->get('/api/netifyd/community/licence')
            ->assertInternalServerError()
            ->assertJson(['message' => 'Cannot create licence']);
    });

    it('renews older licence', function () {
        $licence = [
            'issued_to' => NetifydLicenceType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => now()->addDay()->unix(),
            ],
            'created_at' => [
                'unix' => now()->subDays(3)->unix(),
            ],
        ];
        partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) use ($licence) {
            $mock->expects('listLicences')
                ->andReturn([
                    $licence,
                ]);
            $mock->expects('renewLicence')
                ->with(NetifydLicenceType::ENTERPRISE, 'EXAMPLE-ENTERPRISE-SERIAL')
                ->andReturn($licence);
        });
        withBasicAuth('', '')
            ->get('/api/netifyd/community/licence')
            ->assertOk();
    });

    it('cannot renew licence', function () {
        $licence = [
            'issued_to' => NetifydLicenceType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => now()->addDay()->unix(),
            ],
            'created_at' => [
                'unix' => now()->subDays(3)->unix(),
            ],
        ];
        partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) use ($licence) {
            $mock->expects('listLicences')
                ->andReturn([
                    $licence,
                ]);
            $mock->expects('renewLicence')
                ->with(NetifydLicenceType::ENTERPRISE, 'EXAMPLE-ENTERPRISE-SERIAL')
                ->andThrow(new Exception('Cannot renew licence'));
        });
        withBasicAuth('', '')
            ->get('/api/netifyd/community/licence')
            ->assertInternalServerError()
            ->assertJson(['message' => 'Cannot renew licence']);
    });

});
