<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Events;

use App\Models\Repository;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SnapshotCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly Repository $repository)
    {
    }
}
