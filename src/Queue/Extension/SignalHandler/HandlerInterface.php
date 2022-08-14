<?php

namespace App\Queue\Extension\SignalHandler;

use App\Queue\Extension\SignalListener;

/**
 * Interface HandlerInterface
 */
interface HandlerInterface
{
    public function toListener(): SignalListener;
}