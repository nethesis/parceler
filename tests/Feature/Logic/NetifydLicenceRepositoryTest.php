<?php

use App\Logic\NetifydLicenseRepository;
use App\NetifydLicenseType;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('list license', function () {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['data' => ['hello']])),
    ]);
    expect($repository->listLicenses())
        ->toBe(['hello']);
    Http::assertSent(function (Request $request) {
        return $request->hasHeader('x-api-key')
            && $request->header('x-api-key')[0] == 'api-key'
            && $request->data()['format'] == 'netifyd';
    });
});

it('handles listing license server errors', function () {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['error' => 'error']), 500),
    ]);
    $repository->listLicenses();
})->throws('Could not list licenses from netifyd:');

it('handles failing to create a license', function (NetifydLicenseType $licenseType) {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['error' => 'error']), 500),
    ]);
    $repository->createLicense($licenseType);
})->throws('Could not create license on netifyd:')
    ->with(NetifydLicenseType::cases());

it('handles failing to renew license', function (NetifydLicenseType $licenseType) {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['error' => 'error']), 500),
    ]);
    $repository->renewLicense($licenseType, 'example serial');
})->throws('Could not renew license on netifyd:')
    ->with(NetifydLicenseType::cases());

it('returns configured entitlements', function () {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    $entitlements = $repository->getConfiguredEntitlements();
    expect($entitlements)
        ->toBeArray()
        ->toContain('netify-proc-aggregator')
        ->toContain('netify-proc-flow-actions');
});

it('detects when entitlements have changed', function () {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    $configured = ['netify-proc-aggregator', 'netify-proc-flow-actions'];
    $license = [
        'netify-proc-aggregator' => ['not_valid_after' => '2026-03-19'],
    ];

    expect($repository->entitlementsChanged($license, $configured))->toBeTrue();
});

it('detects when entitlements have not changed', function () {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    $configured = ['netify-proc-aggregator', 'netify-proc-flow-actions'];
    $license = [
        'netify-proc-aggregator' => ['not_valid_after' => '2026-03-19'],
        'netify-proc-flow-actions' => ['not_valid_after' => '2026-03-19'],
    ];

    expect($repository->entitlementsChanged($license, $configured))->toBeFalse();
});

it('deletes a license', function () {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['data' => []])),
    ]);
    $repository->deleteLicense('test-serial');

    Http::assertSent(function (Request $request) {
        return $request->method() === 'DELETE'
            && str_contains($request->url(), '/api/v2/integrator/licenses/test-serial')
            && $request->hasHeader('x-api-key');
    });
});

it('handles failing to delete license', function () {
    $repository = new NetifydLicenseRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['error' => 'error']), 500),
    ]);
    $repository->deleteLicense('test-serial');
})->throws('Could not delete license on netifyd:');
