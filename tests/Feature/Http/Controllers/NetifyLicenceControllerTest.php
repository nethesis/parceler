<?php

use App\Logic\LicenceVerification;
use App\Logic\NetifydLicenseRepository;
use App\NetifydLicenseType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;

use function Pest\Laravel\get;
use function Pest\Laravel\partialMock;
use function Pest\Laravel\withBasicAuth;

describe('middleware checking', function () {
    it('cannot access enterprise license without credentials', function (string $url) {
        get($url)
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Basic');
    })->with([
        '/api/netifyd/enterprise/license',
        '/api/netifyd/community/license',
    ]);

    it('can access free license without credentials', function () {
        Cache::expects('has')->with(NetifydLicenseType::COMMUNITY->cacheLabel())->andReturnTrue();
        Cache::expects('get')->with(NetifydLicenseType::COMMUNITY->cacheLabel())->andReturn(['license_key' => 'cache']);
        get('/api/netifyd/license')
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

    it('can access enterprise license with credentials', function () {
        Cache::expects('has')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturnTrue();
        Cache::expects('get')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturn(['license_key' => 'cache']);
        withBasicAuth('', '')
            ->get('/api/netifyd/community/license')
            ->assertOk()
            ->assertJson(['license_key' => 'cache']);
    });

    it('serves correctly cache if present', function () {
        Cache::expects('has')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturnTrue();
        Cache::expects('get')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturn(['license_key' => 'cached-license-key']);
        Http::preventStrayRequests();
        Http::fake();
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/license')
            ->assertOk()
            ->assertJson([
                'license_key' => 'cached-license-key',
            ]);
    });

    it('handles errors from netifyd server', function () {
        partialMock(NetifydLicenseRepository::class, function (MockInterface $mock) {
            $mock->expects('listLicenses')
                ->andThrow(new Exception('Netifyd server error'));
        });
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/license')
            ->assertInternalServerError()
            ->assertJson([
                'message' => 'Netifyd server error',
            ]);
    });

    it('list licenses', function () {
        $expiration = now()->addDays(2);
        $creation = now()->subDay();
        $license = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => $expiration->unix(),
            ],
            'created_at' => [
                'unix' => $creation->unix(),
            ],
        ];
        partialMock(NetifydLicenseRepository::class, function (MockInterface $mock) use ($license) {
            $mock->expects('listLicenses')
                ->andReturn([$license]);
        });
        Cache::expects('has')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturnFalse();
        Cache::expects('put')->with(NetifydLicenseType::ENTERPRISE->cacheLabel(), $license, ($expiration->unix() - $creation->unix()) / 2);
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/license')
            ->assertOk()
            ->json($license);
    });

    it('license not found', function () {
        partialMock(NetifydLicenseRepository::class, function (MockInterface $mock) {
            $mock->expects('listLicenses')
                ->andReturn([]);
            $mock->expects('createLicense')
                ->with(NetifydLicenseType::ENTERPRISE)
                ->andreturn([]);
        });
        withBasicAuth('system-id', 'secret')->get('/api/netifyd/community/license');
    });

    it('cannot create new license', function () {
        partialMock(NetifydLicenseRepository::class, function (MockInterface $mock) {
            $mock->expects('listLicenses')
                ->andReturn([]);
            $mock->expects('createLicense')
                ->with(NetifydLicenseType::ENTERPRISE)
                ->andThrow(new Exception('Cannot create license'));
        });
        withBasicAuth('', '')
            ->get('/api/netifyd/community/license')
            ->assertInternalServerError()
            ->assertJson(['message' => 'Cannot create license']);
    });

    it('renews older license', function () {
        $license = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => now()->addDay()->unix(),
            ],
            'created_at' => [
                'unix' => now()->subDays(3)->unix(),
            ],
        ];
        partialMock(NetifydLicenseRepository::class, function (MockInterface $mock) use ($license) {
            $mock->expects('listLicenses')
                ->andReturn([
                    $license,
                ]);
            $mock->expects('renewLicense')
                ->with(NetifydLicenseType::ENTERPRISE, 'EXAMPLE-ENTERPRISE-SERIAL')
                ->andReturn($license);
        });
        withBasicAuth('', '')
            ->get('/api/netifyd/community/license')
            ->assertOk();
    });

    it('cannot renew license', function () {
        $license = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => now()->addDay()->unix(),
            ],
            'created_at' => [
                'unix' => now()->subDays(3)->unix(),
            ],
        ];
        partialMock(NetifydLicenseRepository::class, function (MockInterface $mock) use ($license) {
            $mock->expects('listLicenses')
                ->andReturn([
                    $license,
                ]);
            $mock->expects('renewLicense')
                ->with(NetifydLicenseType::ENTERPRISE, 'EXAMPLE-ENTERPRISE-SERIAL')
                ->andThrow(new Exception('Cannot renew license'));
        });
        withBasicAuth('', '')
            ->get('/api/netifyd/community/license')
            ->assertInternalServerError()
            ->assertJson(['message' => 'Cannot renew license']);
    });

});
