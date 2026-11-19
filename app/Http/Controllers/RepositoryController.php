<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Http\Controllers;

use App\Models\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RepositoryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Repository $repository, string $path)
    {
        $fileToDownload = $repository->getStablePath().'/'.$path;

        // When using the local driver, we can just return the file
        if (! Storage::providesTemporaryUrls()) {
            if (Storage::fileMissing($fileToDownload)) {
                abort(404);
            }

            return Storage::download($fileToDownload);
        }

        // This offloads the download directly to the client, a missing file gets its 404 from the storage
        return redirect(Storage::temporaryUrl($fileToDownload, now()->addMinute()));
    }
}
