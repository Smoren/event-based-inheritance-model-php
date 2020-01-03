<?php


namespace Smoren\EventBus;


class Listener
{
    /**
     * @var EventBus шина
     */
    protected $bus;

    /**
     * @var array параметры адресации внутри шины
     */
    protected $params;

    /**
     * @var callable обратный вызов
     */
    protected $callback;

    /**
     * Listener constructor.
     * @param EventBus $bus
     * @param array $params
     * @param callable $callback
     */
    public function __construct(EventBus $bus, array $params, callable $callback)
    {
        $this->bus = $bus;
        $this->params = $params;
        $this->callback = $callback;
    }

    /**
     * @param mixed $params
     * @return mixed
     */
    public function handle(&$params)
    {
        return ($this->callback)($params, $this);
    }

    /**
     * @param mixed $params
     * @return mixed|null
     * @throws EventBusException
     */
    public function handlePrevious(&$params)
    {
        return $this->bus->handlePrevious($this->params, $params);
    }
}