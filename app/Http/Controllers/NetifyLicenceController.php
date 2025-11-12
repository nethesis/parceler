<?php

namespace App\Http\Controllers;

use App\Logic\NetifydLicenceRepository;
use App\NetifydLicenceType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NetifyLicenceController extends Controller
{
    public function community(NetifydLicenceRepository $licenceProvider): JsonResponse
    {
        return $this->run($licenceProvider, NetifydLicenceType::COMMUNITY);
    }

    private function run(NetifydLicenceRepository $licenceProvider, NetifydLicenceType $licenceType): JsonResponse
    {
        // If license is in cache, return it.
        if (Cache::has($licenceType->cacheLabel())) {
            Log::debug('Cache hit, serving license.');

            return response()->json(Cache::get($licenceType->cacheLabel()));
        }

        Log::debug('Cache miss, listing licenses.');
        // Check if the community license is on the remote server.
        try {
            $licences = $licenceProvider->listLicences();
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
        $license = array_find($licences, fn ($item) => $item['issued_to'] == $licenceType->label());
        // If it doesn't exist, create it.
        if ($license == null) {
            Log::debug('Requested license not found, creating it.');
            try {
                $license = $licenceProvider->createLicence($licenceType);
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }
        // Got license, checking if everything is in place.
        Log::debug('License found, checking renewal/expiration.');
        $expiration = $license['expire_at']['unix'];
        $creation = $license['created_at']['unix'];
        $renewalThreshold = ($expiration - $creation) / 2 + $creation;
        $now = now()->unix();
        if ($renewalThreshold < $now) {
            Log::debug('Licence can be renewed, renewing.');
            try {
                $license = $licenceProvider->renewLicence($licenceType, $license['serial']);
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }

        $expiration = $license['expire_at']['unix'];
        $creation = $license['created_at']['unix'];
        Cache::put($licenceType->cacheLabel(), $license, ($expiration - $creation) / 2);

        return response()->json($license);
    }

    public function enterprise(NetifydLicenceRepository $licenceProvider): JsonResponse
    {
        return $this->run($licenceProvider, NetifydLicenceType::ENTERPRISE);
    }
}
