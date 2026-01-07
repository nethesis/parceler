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
        $fakeLicense = [
            'issued_to' => NetifydLicenseType::COMMUNITY->label(),
            'expire_at' => [
                'unix' => now()->addDays(2)->unix(),
            ],
        ];
        Cache::expects('get')->with(NetifydLicenseType::COMMUNITY->cacheLabel())->andReturn($fakeLicense);
        get('/api/netifyd/license')
            ->assertOk()
            ->assertJson($fakeLicense);
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
        $fakeLicense = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'expire_at' => [
                'unix' => now()->addDays(2)->unix(),
            ],
        ];
        Cache::expects('get')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturn($fakeLicense);
        withBasicAuth('', '')
            ->get('/api/netifyd/community/license')
            ->assertOk()
            ->assertJson($fakeLicense);
    });

    it('serves correctly cache if present and not expired', function () {
        $fakeLicense = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'expire_at' => [
                'unix' => now()->addDays(2)->unix(),
            ],
        ];
        Cache::expects('get')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturn($fakeLicense);
        Http::preventStrayRequests();
        Http::fake();
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/license')
            ->assertOk()
            ->assertJson($fakeLicense);
    });

    it('throws away the cache if license is expired', function () {
        $fakeLicense = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'expire_at' => [
                'unix' => now()->subDay()->unix(),
            ],
        ];
        Cache::expects('get')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturn($fakeLicense);
        Cache::expects('forget')->with(NetifydLicenseType::ENTERPRISE->cacheLabel());
        $license = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => now()->addDays(2)->unix(),
            ],
        ];
        Cache::expects('put')->withSomeOfArgs(NetifydLicenseType::ENTERPRISE->cacheLabel(), $license);
        partialMock(NetifydLicenseRepository::class, function (MockInterface $mock) use ($license) {
            $mock->expects('listLicenses')
                ->andReturn([$license]);
        });
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/license')
            ->assertOk()
            ->json($license);
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
        $license = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => now()->addDays(2)->unix(),
            ],
        ];
        partialMock(NetifydLicenseRepository::class, function (MockInterface $mock) use ($license) {
            $mock->expects('listLicenses')
                ->andReturn([$license]);
        });
        Cache::expects('get')->with(NetifydLicenseType::ENTERPRISE->cacheLabel())->andReturnNull();
        Cache::expects('put')->withSomeOfArgs(NetifydLicenseType::ENTERPRISE->cacheLabel(), $license);
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
                ->andThrow(new Exception('License not found'));
        });
        withBasicAuth('system-id', 'secret')
            ->get('/api/netifyd/community/license')
            ->assertServerError()
            ->assertJson(['message' => 'License not found']);
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

    it('cannot renew license, but it\'s not expired', function () {
        $license = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => now()->addDay()->unix(),
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
            ->assertOk()
            ->assertJson($license);
    });

    it('cannot renew license, but it\'s expired', function () {
        $license = [
            'issued_to' => NetifydLicenseType::ENTERPRISE->label(),
            'serial' => 'EXAMPLE-ENTERPRISE-SERIAL',
            'expire_at' => [
                'unix' => now()->subDay()->unix(),
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
            ->assertInternalServerError();
    });

});
