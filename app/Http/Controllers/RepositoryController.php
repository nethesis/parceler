<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RepositoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $type, Repository $repository, string $path)
    {
        // We ensure that the headers are there thanks to the middleware
        $systemId = $request->headers->get('php-auth-user');
        $secret = $request->headers->get('php-auth-pw');

        if (Cache::has($systemId)) {
            Log::debug('Cache hit');
        } else {
            Log::debug('Cache miss, sending request');
            $response = Http::withHeader('Authorization', 'Basic '.base64_encode($systemId.':'.$secret))
                ->get(config("repositories.endpoints.$type").'/auth/product/nethsecurity');
            if ($response->successful()) {
                Log::debug('Caching the request, code received is: '.$response->status());
                Cache::put($systemId, true, now()->addDays(2));
            } else {
                Log::warning('My subscription error, error code returned: '.$response->status());
                abort(401);
            }
        }

        $fileToDownload = $repository->getStablePath().'/'.$path;

        if (Storage::fileMissing($fileToDownload)) {
            abort(404);
        }

        return Storage::download($fileToDownload);
    }
}
