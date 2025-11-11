<?php

namespace App\Logic;

use App\NetifydLicenceType;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class NetifydLicenceRepository
{
    public function __construct(private string $endpoint, private string $apiKey) {}

    /**
     * @throws Exception
     */
    public function listLicences(): array
    {
        try {
            return Http::withHeader('x-api-key', $this->apiKey)
                ->get($this->endpoint.'/api/v2/integrator/licenses?format=netifyd')
                ->throw()
                ->json('data');
        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not list licences from netifyd: '.$e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function createLicence(NetifydLicenceType $licenceType): array
    {
        try {
            return Http::withHeader('x-api-key', $this->apiKey)
                ->post(config('netifyd.endpoint').'/api/v2/integrator/licenses', [
                    'format' => 'object',
                    'issued_to' => $licenceType->label(),
                    'duration_days' => $licenceType->durationDays(),
                    'description' => 'License provided to'.$licenceType->label().'instances.',
                    'entitlements' => [
                        'netify-proc-aggregator',
                        'netify-proc-flow-actions',
                    ],
                ])->throw()
                ->json('data');
        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not create licence on netifyd: '.$e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function renewLicence(NetifydLicenceType $licenceType, string $serial): array
    {
        try {
            return Http::withHeader('x-api-key', config('netifyd.api-key'))
                ->post(config('netifyd.endpoint').'/api/v2/integrator/licenses/'.$serial.'/renew', [
                    'duration_days' => $licenceType->durationDays(),
                ])->throw()
                ->json('data');

        } catch (ConnectionException|RequestException $e) {
            throw new Exception('Could not renew licence on netifyd: '.$e->getMessage());
        }
    }
}
