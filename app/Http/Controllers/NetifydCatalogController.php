<?php

namespace App\Http\Controllers;

use App\Logic\NetifydCatalogRepository;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class NetifydCatalogController extends Controller
{
    public function applicationsCatalog(NetifydCatalogRepository $catalog): Response|RedirectResponse
    {
        try {
            return $this->serveCatalog($catalog->applicationsCatalog());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function applicationsCategories(NetifydCatalogRepository $catalog): Response|RedirectResponse
    {
        try {
            return $this->serveCatalog($catalog->applicationsCategories());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function protocolsCatalog(NetifydCatalogRepository $catalog): Response|RedirectResponse
    {
        try {
            return $this->serveCatalog($catalog->protocolsCatalog());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function protocolsCategories(NetifydCatalogRepository $catalog): Response|RedirectResponse
    {
        try {
            return $this->serveCatalog($catalog->protocolsCategories());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function serveCatalog(string $path): Response|RedirectResponse
    {
        if (! Storage::providesTemporaryUrls()) {
            return Storage::download($path);
        }

        return redirect(Storage::temporaryUrl($path, now()->addMinute()));
    }
}
