<?php

namespace App\Logic;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenceVerification
{
    public function __construct(public readonly string $enterpriseEndpoint, public readonly string $communityEntrypoint) {}

    public function communityCheck(string $systemId, string $secret): bool
    {
        if (Cache::has($systemId)) {
            Log::debug('Cache hit');
        } else {
            Log::debug('Cache miss, sending request');
            try {
                $response = Http::withHeader('Authorization', 'token '.$secret)
                    ->get($this->communityEntrypoint)
                    ->throw();
                $validUntil = $response->json('subscription.valid_until');
                if ($validUntil == null) {
                    Log::error('Invalid response from the server.');

                    return false;
                } else {
                    $validUntil = Carbon::parse($validUntil);
                    if (now()->greaterThan($validUntil)) {
                        Log::warning("Subscription for $systemId is expired.");

                        return false;
                    }
                }
                Log::debug('Subscription is valid, caching the request until expiry.');
                Cache::put($systemId, true, $response->json('subscription.valid_until', $validUntil));
            } catch (ConnectionException $e) {
                Log::error('Connection error: '.$e->getMessage());

                return false;
            } catch (RequestException $e) {
                Log::warning('My subscription error, error code returned: '.$e->response->status());

                return false;
            }
        }

        return true;
    }

    public function enterpriseCheck(string $systemId, string $secret): bool
    {
        if (Cache::has($systemId)) {
            Log::debug('Cache hit');
        } else {
            Log::debug('Cache miss, sending request');
            try {
                $response = Http::withHeader('Authorization', 'Basic '.base64_encode($systemId.':'.$secret))
                    ->get($this->enterpriseEndpoint)
                    ->throw();
                Log::debug('Caching the request, code received is: '.$response->status());
                Cache::put($systemId, true, now()->addDays(2));
            } catch (ConnectionException $e) {
                Log::error('Connection error: '.$e->getMessage());

                return false;
            } catch (RequestException $e) {
                Log::warning("My subscription error for $systemId, error code returned: ".$e->response->status());

                return false;
            }
        }

        return true;
    }
}
