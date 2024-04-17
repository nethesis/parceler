<?php

use App\Models\Repository;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;
use function Pest\Laravel\withBasicAuth;

dataset('repositories', function () {
    return ['enterprise', 'community'];
});

beforeEach(function () {
    Storage::fake('local');
});

it('returns not found if wrong repo')
    ->get('repository/hello_world/nethsecurity/packages/x86_64/base/Packages')
    ->assertNotFound();

it('cannot access route without base auth', function ($repository) {
    $repo = Repository::factory()->create();
    get("/repository/$repository/$repo->name/packages/x86_64/base/Packages")
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Basic');
})->with('repositories');

it('retrieves ok from upstream', function ($repository) {
    // Setup auth
    $uuid = fake()->uuid();
    $secret = fake()->password();
    $token = base64_encode("$uuid:$secret");
    // Cache mock
    Cache::shouldReceive('has')
        ->with($uuid)
        ->andReturnFalse();
    Cache::shouldReceive('put')
        ->withSomeOfArgs($uuid, true)
        ->andReturnTrue();
    // Http Mock
    Http::preventStrayRequests();
    $endpoint = config("repositories.endpoints.$repository");
    Http::fake([
        $endpoint.'/*' => Http::response(),
    ]);
    $repo = Repository::factory()->create();
    // Backend request
    withBasicAuth($uuid, $secret)
        ->get("/repository/$repository/$repo->name/packages/x86_64/base/Packages")
        ->assertNotFound();
    // Http assertions
    Http::assertSent(function (Request $request) use ($endpoint, $token) {
        return $request->hasHeader('Authorization', 'Basic '.$token)
            && $request->url() == $endpoint.'/auth/product/nethsecurity';
    });
})->with('repositories');

it('retrieves error from upstream', function ($repository) {
    // Setup auth
    $uuid = fake()->uuid();
    $secret = fake()->password();
    $token = base64_encode("$uuid:$secret");
    // Cache mock
    Cache::shouldReceive('has')
        ->with($uuid)
        ->andReturnFalse();
    // Http Mock
    Http::preventStrayRequests();
    $endpoint = config("repositories.endpoints.$repository");
    Http::fake([
        $endpoint.'/*' => Http::response(status: 401),
    ]);
    $repo = Repository::factory()->create();
    // Backend request
    withBasicAuth($uuid, $secret)
        ->get("/repository/$repository/$repo->name/packages/x86_64/base/Packages")
        ->assertUnauthorized();
    // Http assertions
    Http::assertSent(function (Request $request) use ($endpoint, $token) {
        return $request->hasHeader('Authorization', 'Basic '.$token)
            && $request->url() == $endpoint.'/auth/product/nethsecurity';
    });
})->with('repositories');

it('downloads file', function ($repository) {
    // this tests the cache process
    $uuid = fake()->uuid();
    Cache::shouldReceive('has')
        ->with($uuid)
        ->andReturnTrue();

    $repo = Repository::factory()->create();
    $stablePath = $repo->getStablePath().now()->toAtomString();
    Storage::createDirectory($stablePath);
    $file = UploadedFile::fake()->create('Packages');
    Storage::putFileAs($stablePath.'/packages/x86_64/base', $file, 'Packages');
    withBasicAuth($uuid, '')
        ->get("/repository/$repository/$repo->name/packages/x86_64/base/Packages")
        ->assertSuccessful();
})->with('repositories');
