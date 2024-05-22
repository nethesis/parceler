<?php

use App\Logic\LicenceVerification;
use App\Models\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

use function Pest\Laravel\get;
use function Pest\Laravel\withBasicAuth;

it('cannot access route without base auth', function () {
    $repo = Repository::factory()->create();
    get("/repository/enterprise/$repo->name/packages/x86_64/base/Packages")
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Basic');
    get("/repository/community/$repo->name/packages/x86_64/base/Packages")
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Basic');
});

describe('valid licence provided', function () {
    beforeEach(function () {
        $this->mock(LicenceVerification::class, function (MockInterface $mock) {
            $mock->shouldReceive('communityCheck', 'enterpriseCheck')
                ->andReturnTrue();
        });
    });

    it('downloads file', function () {
        Storage::fake();
        $uuid = fake()->uuid();
        $repo = Repository::factory()->create();
        $stablePath = $repo->snapshotDir().'/'.now()->toAtomString();
        Storage::createDirectory($stablePath);
        $file = UploadedFile::fake()->create('Packages');
        Storage::putFileAs($stablePath.'/packages/x86_64/base', $file, 'Packages');
        withBasicAuth($uuid, '')
            ->get("/repository/community/$repo->name/packages/x86_64/base/Packages")
            ->assertRedirect();
    });

    it('returns not found if file missing', function () {
        $uuid = fake()->uuid();
        $repo = Repository::factory()->create();
        withBasicAuth($uuid, '')
            ->get("/repository/enterprise/$repo->name/packages/x86_64/base/Packages")
            ->assertNotFound();
    });
});

it('unauthorized if licence check fails', function () {
    $this->mock(LicenceVerification::class, function (MockInterface $mock) {
        $mock->shouldReceive('communityCheck', 'enterpriseCheck')
            ->andReturnFalse();
    });
    $uuid = fake()->uuid();
    $repo = Repository::factory()->create();
    withBasicAuth($uuid, '')
        ->get("/repository/community/$repo->name/packages/x86_64/base/Packages")
        ->assertUnauthorized();
    withBasicAuth($uuid, '')
        ->get("/repository/enterprise/$repo->name/packages/x86_64/base/Packages")
        ->assertUnauthorized();
});
