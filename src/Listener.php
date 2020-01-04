<?php


namespace Smoren\EventBasedInheritanceModel;


/**
 * Класс обработчика события
 * @package Smoren\EventBasedInheritanceModel
 * @author Smoren <ofigate@gmail.com>
 */
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
     * @param EventBus $bus объект шины событий
     * @param array $params внутренные параметры обработчика
     * @param callable $callback функция-обработчик
     */
    public function __construct(EventBus $bus, array $params, callable $callback)
    {
        $this->bus = $bus;
        $this->params = $params;
        $this->callback = $callback;
    }

    /**
     * Запускает обработчик события
     * @param mixed $params дополнительные параметры события
     * @return mixed результат, возвращаемый функцией-обработчиком
     */
    public function handle(&$params)
    {
        return ($this->callback)($params, $this);
    }

    /**
     * Вызывает предыдущий обработчик события в стеке
     * @param mixed $params дополнительные параметры события
     * @return mixed|null результат, возвращаемый запущенным обработчиком, либо null, если дошли до дна стека
     * @throws EventBusException
     */
    public function handlePrevious(&$params)
    {
        return $this->bus->handlePrevious($this->params, $params);
    }
}