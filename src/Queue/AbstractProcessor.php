<?php

namespace App\Queue;

use Interop\Queue\Context;
use Interop\Queue\Message;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class AbstractProcessor
 */
abstract class AbstractProcessor implements ProcessorInterface
{
    protected LoggerInterface $logger;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Allow the processor to be called by invoking it
     *
     * This method is used for automatically setting up the queue
     *
     * @param Message $message
     * @param Context $context
     *
     * @return object|string
     */
    public function __invoke(Message $message, Context $context)
    {
        $this->logger->debug('Received message', [
            'message' => $message->getCorrelationId(),
            'body' => $message->getBody(),
            'headers' => $message->getHeaders(),
        ]);

        try {
            return $this->process($message, $context);
        } catch (Throwable $throwable) {
            $this->logger->error($throwable->getMessage(), $throwable->getTrace());
            if ($message->isRedelivered()) {
                $serializedMessage = [
                    'id' => $message->getMessageId(),
                    'published_at' => $message->getTimestamp(),
                    'body' => $message->getBody(),
                    'headers' => $message->getHeaders(),
                    'properties' => $message->getProperties(),
                    'priority' => -1 * $message->getPriority(),
                    'correlation_id' => $message->getCorrelationId(),
                    'queue' => get_class($this), // returns the child class and therefore the queue name
                ];
                $this->logger->alert('Redelivered Message failed', ['message' => $serializedMessage]);

                return self::REJECT;
            }

            return self::REQUEUE;
        }
    }
}
