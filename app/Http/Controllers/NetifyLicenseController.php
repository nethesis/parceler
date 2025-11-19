<?php

namespace App\Http\Controllers;

use App\Logic\NetifydLicenseRepository;
use App\NetifydLicenseType;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NetifyLicenseController extends Controller
{
    public function community(NetifydLicenseRepository $licenseProvider): JsonResponse
    {
        return $this->run($licenseProvider, NetifydLicenseType::COMMUNITY);
    }

    private function run(NetifydLicenseRepository $licenseProvider, NetifydLicenseType $licenseType): JsonResponse
    {
        // If license is in cache, return it.
        if (Cache::has($licenseType->cacheLabel())) {
            Log::debug('Requested netifyd license found in cache, returning it.');

            return response()->json(Cache::get($licenseType->cacheLabel()));
        }

        Log::debug('Requested netifyd license not found in cache, checking remote server.');
        // Check if the community license is on the remote server.
        try {
            $licenses = $licenseProvider->listLicenses();
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
        $license = array_find($licenses, fn ($item) => $item['issued_to'] == $licenseType->label());
        // If it doesn't exist, create it.
        if ($license == null) {
            Log::debug('Netifyd license not found on remote server, creating it.');
            try {
                $license = $licenseProvider->createLicense($licenseType);
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }
        // Got license, checking if everything is in place.
        Log::debug('Netifyd license recovered from remote server, checking if it can be renewed.');
        $expiration = Carbon::createFromTimestampUTC($license['expire_at']['unix'])->startOfDay()->toImmutable();
        $creation = Carbon::createFromTimestampUTC($license['created_at']['unix'])->startOfDay()->toImmutable();
        $diff = $creation->diff($expiration)->cascade()->totalDays;
        $renewalThreshold = $creation->addDays(ceil($diff / 2));
        $now = now()->utc()->startOfDay();
        if ($renewalThreshold <= $now) {
            Log::debug('Netifyd license can be renewed, renewing it.');
            try {
                $license = $licenseProvider->renewLicense($licenseType, $license['serial']);
            } catch (Exception $e) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }
        Cache::put($licenseType->cacheLabel(), $license, now()->addHour());

        return response()->json($license);
    }

    public function enterprise(NetifydLicenseRepository $licenseProvider): JsonResponse
    {
        return $this->run($licenseProvider, NetifydLicenseType::ENTERPRISE);
    }
}
