<?php

use App\Logic\LicenceVerification;
use App\Logic\PackageRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;

use function Pest\Laravel\get;
use function Pest\Laravel\withBasicAuth;

it('cannot access route without base auth', function () {
    get('/repository/enterprise/nethsecurity/packages/x86_64/base/Packages')
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Basic');
    get('/repository/community/nethsecurity/packages/x86_64/base/Packages')
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
        $repository = app(PackageRepository::class);
        $snapshot = now()->toAtomString();
        $file = UploadedFile::fake()->create('Packages');
        Storage::putFileAs($repository->snapshotDir()."/$snapshot/packages/x86_64/base", $file, 'Packages');
        $repository->setCurrent($snapshot);
        withBasicAuth($uuid, '')
            ->get('/repository/community/nethsecurity/packages/x86_64/base/Packages')
            ->assertRedirect();
    });

    it('returns not found if file missing', function () {
        Storage::fake();
        $uuid = fake()->uuid();
        app(PackageRepository::class)->setCurrent(now()->toAtomString());
        withBasicAuth($uuid, '')
            ->get('/repository/enterprise/nethsecurity/packages/x86_64/base/Packages')
            ->assertNotFound();
    });

    it('returns not found if no snapshot is served', function () {
        Storage::fake();
        $uuid = fake()->uuid();
        withBasicAuth($uuid, '')
            ->get('/repository/enterprise/nethsecurity/packages/x86_64/base/Packages')
            ->assertNotFound();
    });

    it('returns not found for another repository', function () {
        Storage::fake();
        $uuid = fake()->uuid();
        $repository = app(PackageRepository::class);
        $snapshot = now()->toAtomString();
        Storage::put($repository->snapshotDir()."/$snapshot/packages/x86_64/base/Packages", 'content');
        $repository->setCurrent($snapshot);
        withBasicAuth($uuid, '')
            ->get('/repository/community/other/packages/x86_64/base/Packages')
            ->assertNotFound();
    });
});

it('unauthorized if licence check fails', function () {
    $this->mock(LicenceVerification::class, function (MockInterface $mock) {
        $mock->shouldReceive('communityCheck', 'enterpriseCheck')
            ->andReturnFalse();
    });
    $uuid = fake()->uuid();
    withBasicAuth($uuid, '')
        ->get('/repository/community/nethsecurity/packages/x86_64/base/Packages')
        ->assertUnauthorized();
    withBasicAuth($uuid, '')
        ->get('/repository/enterprise/nethsecurity/packages/x86_64/base/Packages')
        ->assertUnauthorized();
});
