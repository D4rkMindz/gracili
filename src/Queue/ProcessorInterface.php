<?php

namespace App\Queue;

use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;

/**
 * Interface MessageInterface
 */
interface ProcessorInterface extends Processor
{
    public function __invoke(Message $message, Context $context);
}