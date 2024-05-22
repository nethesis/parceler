<?php

use App\Logic\LicenceVerification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->licenceVerification = new LicenceVerification('http://127.0.0.1/enterprise', 'http://127.0.0.1/community');
    $this->systemId = fake()->uuid();
    $this->secret = fake()->password();
});

describe('enterprise licence verification', function () {
    it('should return true if cache is hit', function () {
        Http::fake();
        Cache::shouldReceive('has')
            ->with($this->systemId)
            ->andReturn(true);

        expect($this->licenceVerification->enterpriseCheck($this->systemId, $this->secret))
            ->toBeTrue();

        Http::assertNothingSent();
    });

    it('should call the endpoint and cache the response', function () {
        Http::fake([
            'http://127.0.0.1/enterprise' => Http::response(),
        ]);

        expect($this->licenceVerification->enterpriseCheck($this->systemId, $this->secret))
            ->toBeTrue()
            ->and(Cache::get($this->systemId))
            ->toBeTrue();

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Basic '.base64_encode($this->systemId.':'.$this->secret));
        });
    });

    it('can handle upstream server exceptions', function () {
        Http::fake([
            'http://127.0.0.1/enterprise' => Http::response([], 500),
        ]);

        expect($this->licenceVerification->enterpriseCheck($this->systemId, $this->secret))
            ->toBeFalse();
    });

    it('can handle http exceptions', function () {
        expect($this->licenceVerification->enterpriseCheck($this->systemId, $this->secret))
            ->toBeFalse();
    });
});

describe('community licence verification', function () {
    it('should hit cache', function () {
        Http::fake();
        Cache::shouldReceive('has')
            ->with($this->systemId)
            ->andReturn(true);

        expect($this->licenceVerification->communityCheck($this->systemId, $this->secret))
            ->toBeTrue();

        Http::assertNothingSent();
    });

    it('should call the endpoint and cache the response', function () {
        $validUntil = now()->addDay();
        Http::fake([
            'http://127.0.0.1/community' => Http::response(json_encode([
                'subscription' => [
                    'valid_until' => $validUntil->toISOString(),
                ],
            ])),
        ]);
        Cache::spy();
        expect($this->licenceVerification->communityCheck($this->systemId, $this->secret))
            ->toBeTrue();
        Cache::shouldHaveReceived('has')
            ->with($this->systemId)
            ->once();
        Cache::shouldHaveReceived('put')
            ->once();
    });

    it('should receive an expired response', function () {
        $validUntil = now()->subDay();
        Http::fake([
            'http://127.0.0.1/community' => Http::response(json_encode([
                'subscription' => [
                    'valid_until' => $validUntil->toISOString(),
                ],
            ])),
        ]);
        Cache::spy();
        expect($this->licenceVerification->communityCheck($this->systemId, $this->secret))
            ->toBeFalse();
        Cache::shouldHaveReceived('has')
            ->with($this->systemId)
            ->once();
        Cache::shouldHaveNotReceived('put');
    });

    it('can handle upstream server exceptions', function () {
        Http::fake([
            'http://127.0.0.1/community' => Http::response([], 500),
        ]);

        expect($this->licenceVerification->communityCheck($this->systemId, $this->secret))
            ->toBeFalse();
    });

    it('can handle http exceptions', function () {
        expect($this->licenceVerification->communityCheck($this->systemId, $this->secret))
            ->toBeFalse();
    });

    it('can handle invalid response from server', function () {
        Http::fake([
            'http://127.0.0.1/community' => Http::response(json_encode([
                'subscription' => [],
            ])),
        ]);
        Cache::spy();
        expect($this->licenceVerification->communityCheck($this->systemId, $this->secret))
            ->toBeFalse();
        Cache::shouldHaveReceived('has')
            ->with($this->systemId)
            ->once();
        Cache::shouldHaveNotReceived('put');
    });
});
