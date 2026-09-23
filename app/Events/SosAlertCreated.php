<?php

namespace App\Events;

use App\Models\SosAlert;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SosAlertCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public SosAlert $alert) {}
}
