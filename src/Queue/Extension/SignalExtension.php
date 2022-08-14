<?php

namespace App\Queue\Extension;

use Enqueue\Consumption\Context\PostConsume;
use Enqueue\Consumption\Context\PostMessageReceived;
use Enqueue\Consumption\Context\PreConsume;
use Enqueue\Consumption\Context\Start;
use Enqueue\Consumption\Exception\LogicException;
use Enqueue\Consumption\Extension\SignalExtension as EnqueueSignalExtension;
use Enqueue\Consumption\PostConsumeExtensionInterface;
use Enqueue\Consumption\PostMessageReceivedExtensionInterface;
use Enqueue\Consumption\PreConsumeExtensionInterface;
use Enqueue\Consumption\StartExtensionInterface;
use Psr\Log\LoggerInterface;

class SignalExtension extends EnqueueSignalExtension implements StartExtensionInterface, PreConsumeExtensionInterface, PostMessageReceivedExtensionInterface, PostConsumeExtensionInterface
{
    public const START = 'start';
    public const PRE_CONSUME = 'pre-consume';
    public const POST_MESSAGE_RECEIVED = 'post-message-received';
    public const POST_CONSUME = 'post-consume';
    public const ABORT = 'abort';
    private array $callbacks;

    /**
     * Constructor
     *
     * @param Array<SignalListener> $callbacks
     */
    public function __construct(array $callbacks)
    {
        $this->callbacks = $callbacks;
    }

    /**
     * On start method
     *
     * @param Start $context
     *
     * @return void
     * @throws LogicException
     */
    public function onStart(Start $context): void
    {
        parent::onStart($context);

        if (!extension_loaded('pcntl')) {
            throw new LogicException('The pcntl extension is required in order to catch signals.');
        }
        pcntl_async_signals(true);

        pcntl_signal(SIGTERM, [$this, 'handleSignal']);
        pcntl_signal(SIGQUIT, [$this, 'handleSignal']);
        pcntl_signal(SIGINT, [$this, 'handleSignal']);

        $this->callListenersForEvent(SignalExtension::START, $context->getLogger(), $context);
    }

    /**
     * Call all listeners in order for an event
     *
     * @param string          $event
     * @param LoggerInterface $logger
     * @param                 $context
     *
     * @return void
     */
    protected function callListenersForEvent(string $event, LoggerInterface $logger, $context): void
    {
        $listeners = $this->getListenersForEvent($event);
        foreach ($listeners as $listener) {
            $callback = $listener->getCallback();

            $result = $callback($logger, $context);
            $logger->debug('Handler ' . $listener->getName() . ' returned ' . $result, ['result' => $result]);
        }
    }

    /**
     * Get all listeners in order for an event
     *
     * @param string $event The event to listen on
     *
     * @return array<SignalListener>
     */
    private function getListenersForEvent(string $event): array
    {
        $listeners = [];
        foreach ($this->callbacks as $listener) {
            if ($listener->getEvent() === $event) {
                $order = $listener->getOrder();
                if (isset($listeners[$order])) {
                    $this->logger->error('Listener for event ' . $event . 'cannot be placed in order ' . $order . ' since this order is already taken. Only one listener is allowed per order');
                }
                $listeners[$order] = $listener;
            }
        }

        return $listeners;
    }

    public function onPreConsume(PreConsume $context): void
    {
        parent::onPreConsume($context);
        $this->callListenersForEvent(SignalExtension::PRE_CONSUME, $context->getLogger(), $context);
    }

    public function onPostMessageReceived(PostMessageReceived $context): void
    {
        parent::onPostMessageReceived($context);
        $this->callListenersForEvent(SignalExtension::POST_MESSAGE_RECEIVED, $context->getLogger(), $context);
    }

    public function onPostConsume(PostConsume $context): void
    {
        parent::onPostConsume($context);
        $this->callListenersForEvent(SignalExtension::POST_CONSUME, $context->getLogger(), $context);
    }

    public function handleSignal(int $signal): void
    {
        parent::handleSignal($signal);
        $this->callListenersForEvent(SignalExtension::ABORT, $this->logger, $signal);
    }
}
