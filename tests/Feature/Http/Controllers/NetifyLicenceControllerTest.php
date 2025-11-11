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

it('cannot access enterprise licence without credentials', function () {
    get('/netifyd/enterprise/licence')
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Basic');
});

it('can access enterprise licence with credentials', function () {
    partialMock(LicenceVerification::class, function (MockInterface $mock) {
        $mock->expects('enterpriseCheck')
            ->with('system-id', 'secret')
            ->andReturnTrue();
    });
    Cache::expects('has')->with(NetifydLicenceType::ENTERPRISE->cacheLabel())->andReturnTrue();
    Cache::expects('get')->with(NetifydLicenceType::ENTERPRISE->cacheLabel())->andReturn(['license_key' => 'cache']);
    withBasicAuth('system-id', 'secret')
        ->get('/netifyd/enterprise/licence')
        ->assertOk()
        ->assertJson(['license_key' => 'cache']);
});

it('serves correctly cache if present', function () {
    Cache::expects('has')->with(NetifydLicenceType::COMMUNITY->cacheLabel())->andReturnTrue();
    Cache::expects('get')->with(NetifydLicenceType::COMMUNITY->cacheLabel())->andReturn(['license_key' => 'cached-license-key']);
    Http::preventStrayRequests();
    Http::fake();
    $response = get('/netifyd/community/licence');
    $response->assertOk()
        ->assertJson([
            'license_key' => 'cached-license-key',
        ]);
});

it('handles errors from netifyd server', function () {
    partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) {
        $mock->expects('listLicences')
            ->andThrow(new Exception('Netifyd server error'));
    });
    get('/netifyd/community/licence')
        ->assertInternalServerError()
        ->assertJson([
            'message' => 'Netifyd server error',
        ]);
});

it('list licences', function () {
    $expiration = now()->addDays(2);
    $creation = now()->subDay();
    $licence = [
        'issued_to' => NetifydLicenceType::COMMUNITY->label(),
        'serial' => 'EXAMPLE-COMMUNITY-SERIAL',
        'expire_at' => [
            'unix' => $expiration->unix(),
        ],
        'created_at' => [
            'unix' => $creation->unix(),
        ],
    ];
    partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) use ($licence) {
        $mock->expects('listLicences')
            ->andReturn([
                'data' => [$licence],
            ]);
    });
    Cache::expects('has')->with(NetifydLicenceType::COMMUNITY->cacheLabel())->andReturnFalse();
    Cache::expects('put')->with(NetifydLicenceType::COMMUNITY->cacheLabel(), $licence, ($expiration->unix() - $creation->unix()) / 2);
    get('/netifyd/community/licence')->assertOk()->json($licence);
});

it('licence not found', function () {
    partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) {
        $mock->expects('listLicences')
            ->andReturn([
                'data' => [],
            ]);
        $mock->expects('createLicence')
            ->with(NetifydLicenceType::COMMUNITY)
            ->andreturn([]);
    });
    get('/netifyd/community/licence');
});

it('cannot create new licence', function () {
    partialMock(NetifydLicenceRepository::class, function (MockInterface $mock) {
        $mock->expects('listLicences')
            ->andReturn([
                'data' => [],
            ]);
        $mock->expects('createLicence')
            ->with(NetifydLicenceType::COMMUNITY)
            ->andThrow(new Exception('Cannot create licence'));
    });
    get('/netifyd/community/licence')
        ->assertInternalServerError()
        ->assertJson(['message' => 'Cannot create licence']);
});

it('renews older licence', function () {
    $licence = [
        'issued_to' => NetifydLicenceType::COMMUNITY->label(),
        'serial' => 'EXAMPLE-COMMUNITY-SERIAL',
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
                'data' => [
                    $licence,
                ],
            ]);
        $mock->expects('renewLicence')
            ->with(NetifydLicenceType::COMMUNITY, 'EXAMPLE-COMMUNITY-SERIAL')
            ->andReturn($licence);
    });
    get('/netifyd/community/licence')
        ->assertOk();
});

it('cannot renew licence', function () {
    $licence = [
        'issued_to' => NetifydLicenceType::COMMUNITY->label(),
        'serial' => 'EXAMPLE-COMMUNITY-SERIAL',
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
                'data' => [
                    $licence,
                ],
            ]);
        $mock->expects('renewLicence')
            ->with(NetifydLicenceType::COMMUNITY, 'EXAMPLE-COMMUNITY-SERIAL')
            ->andThrow(new Exception('Cannot renew licence'));
    });
    get('/netifyd/community/licence')
        ->assertInternalServerError()
        ->assertJson(['message' => 'Cannot renew licence']);
});
