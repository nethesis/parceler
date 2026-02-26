<?php

namespace App\Http\Controllers;

use App\Logic\NetifydCatalogRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class NetifydCatalogController extends Controller
{
    public function applicationsCatalog(NetifydCatalogRepository $catalog): JsonResponse
    {
        try {
            return Response::json($catalog->applicationsCatalog());
        } catch (Exception $e) {
            return Response::json(['message' => $e->getMessage()], 500);
        }
    }

    public function applicationsCategories(NetifydCatalogRepository $catalog): JsonResponse
    {
        try {
            return Response::json(data: $catalog->applicationsCategories());
        } catch (Exception $e) {
            return Response::json(['message' => $e->getMessage()], 500);
        }
    }

    public function protocolsCatalog(NetifydCatalogRepository $catalog): JsonResponse
    {
        try {
            return Response::json($catalog->protocolsCatalog());
        } catch (Exception $e) {
            return Response::json(['message' => $e->getMessage()], 500);
        }
    }

    public function protocolsCategories(NetifydCatalogRepository $catalog): JsonResponse
    {
        try {
            return Response::json($catalog->protocolsCategories());
        } catch (Exception $e) {
            return Response::json(['message' => $e->getMessage()], 500);
        }
    }
}
