<?php

namespace App\Logic;

use App\Entitlements;
use App\NetifydLicenseType;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class NetifydLicenseRepository
{
    public function __construct(private string $endpoint, private string $apiKey) {}

    /**
     * @throws Exception
     */
    public function listLicenses(): array
    {
        try {
            return Http::withHeader('x-api-key', $this->apiKey)
                ->get($this->endpoint.'/api/v2/integrator/licenses?format=netifyd')
                ->throw()
                ->json('data');
        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not list licenses from netifyd: '.$e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function createLicense(NetifydLicenseType $licenseType): array
    {
        try {
            return Http::withHeader('x-api-key', $this->apiKey)
                ->post(config('netifyd.endpoint').'/api/v2/integrator/licenses', [
                    'format' => 'object',
                    'issued_to' => $licenseType->label(),
                    'duration_days' => $licenseType->durationDays(),
                    'description' => 'License provided to '.$licenseType->label().' instances.',
                    'entitlements' => $this->getConfiguredEntitlements(),
                ])->throw()
                ->json('data');
        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not create license on netifyd: '.$e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function renewLicense(NetifydLicenseType $licenseType, string $serial): array
    {
        try {
            return Http::withHeader('x-api-key', config('netifyd.api-key'))
                ->post(config('netifyd.endpoint').'/api/v2/integrator/licenses/'.$serial.'/renew', [
                    'expire_at' => now()->utc()->startOfDay()->addDays($licenseType->durationDays())->subDay()->toDateString(),
                ])->throw()
                ->json('data');

        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not renew license on netifyd: '.$e->getMessage());
        }
    }

    /**
     * Get configured entitlements.
     *
     * @return array<string>
     */
    public function getConfiguredEntitlements(): array
    {
        return Entitlements::all();
    }

    /**
     * Check if any configured entitlement is missing from the license response.
     */
    public function entitlementsChanged(array $license, array $configuredEntitlements): bool
    {
        foreach ($configuredEntitlements as $entitlement) {
            if (! array_key_exists($entitlement, $license)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete a license by serial.
     *
     * @throws Exception
     */
    public function deleteLicense(string $serial): void
    {
        try {
            Http::withHeader('x-api-key', $this->apiKey)
                ->delete(config('netifyd.endpoint').'/api/v2/integrator/licenses/'.$serial)
                ->throw();
        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not delete license on netifyd: '.$e->getMessage());
        }
    }
}
