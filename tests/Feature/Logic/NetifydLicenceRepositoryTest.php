<?php

use App\Logic\NetifydLicenceRepository;
use App\NetifydLicenceType;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('list licences', function () {
    $repository = new NetifydLicenceRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['data' => ['hello']])),
    ]);
    expect($repository->listLicences())
        ->toBe(['hello']);
    Http::assertSent(function (Request $request) {
        return $request->hasHeader('x-api-key')
            && $request->header('x-api-key')[0] == 'api-key'
            && $request->data()['format'] == 'netifyd';
    });
});

it('handles listing licences server errors', function () {
    $repository = new NetifydLicenceRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['error' => 'error']), 500),
    ]);
    $repository->listLicences();
})->throws('Could not list licences from netifyd:');

it('handles failing to create a licence', function (NetifydLicenceType $licenceType) {
    $repository = new NetifydLicenceRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['error' => 'error']), 500),
    ]);
    $repository->createLicence($licenceType);
})->throws('Could not create licence on netifyd:')
    ->with(NetifydLicenceType::cases());

it('handles failing to renew licence', function (NetifydLicenceType $licenceType) {
    $repository = new NetifydLicenceRepository('http://127.0.0.1', 'api-key');
    Http::fake([
        '*' => Http::response(json_encode(['error' => 'error']), 500),
    ]);
    $repository->renewLicence($licenceType, 'dummy');
})->throws('Could not renew licence on netifyd:')
    ->with(NetifydLicenceType::cases());
