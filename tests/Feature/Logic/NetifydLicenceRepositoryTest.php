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
    $repository->renewLicense($licenseType, 'example serial', now());
})->throws('Could not renew license on netifyd:')
    ->with(NetifydLicenseType::cases());
