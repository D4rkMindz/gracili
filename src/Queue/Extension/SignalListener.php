<?php

namespace App\Queue\Extension;

/**
 * Class SignalListener
 */
class SignalListener
{
    private string $name;
    private string $event;
    private $callback;
    private int $order;

    /**
     * @param string   $name     The name of the listener (must be unique)
     * @param string   $event    The event to listen to (is defined on the signal extension)
     * @param callable $callback The callback to execute. The callback has the logger as parameter
     * @param int      $order    The order in which the callbacks (if many are defined) should be called
     */
    public function __construct(string $name, string $event, callable $callback, int $order)
    {
        $this->name = $name;
        $this->event = $event;
        $this->callback = $callback;
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @return int
     */
    public function getOrder(): int
    {
        return $this->order;
    }
}