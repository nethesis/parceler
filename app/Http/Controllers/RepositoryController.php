<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Http\Controllers;

use App\Logic\PackageRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RepositoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, PackageRepository $repository, string $path)
    {
        $servingDir = $repository->servingDir();
        if (is_null($servingDir)) {
            abort(404);
        }

        $fileToDownload = $servingDir.'/'.$path;

        if (Storage::fileMissing($fileToDownload)) {
            abort(404);
        }

        // When using the local driver, we can just return the file
        if (! Storage::providesTemporaryUrls()) {
            return Storage::download($fileToDownload);
        }

        // This offloads the download directly to the client
        return redirect(Storage::temporaryUrl($fileToDownload, now()->addMinute()));
    }
}
