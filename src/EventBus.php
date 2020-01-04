<?php


namespace Smoren\EventBasedInheritanceModel;

/**
 * Класс, реализующий шину событий с цепочечно-наследуемыми обработчиками
 * @package Smoren\EventBasedInheritanceModel
 * @author Smoren <ofigate@gmail.com>
 */
class EventBus
{
    /**
     * @var Listener[][] Карта стеков обработчиков событий по имени события
     */
    protected $listeners = [];

    /**
     * EventBus constructor.
     * @param array $defaultListeners карта дефолтных обработчиков событий по имени события
     */
    public function __construct(array $defaultListeners = [])
    {
        /**
         * @var string $eventName имя события
         * @var Listener $callback функция-обработчик
         */
        foreach($defaultListeners as $eventName => $callback) {
            $this->addListener($eventName, $callback);
        }
    }

    /**
     * Добавляет обработчик событий в стек
     * @param string $eventName имя события
     * @param callable $callback функция-обработчик
     * @return $this
     */
    public function addListener(string $eventName, callable $callback): self
    {
        if(!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $params = [
            'eventName' => $eventName,
            'index' => sizeof($this->listeners[$eventName]),
        ];
        $this->listeners[$eventName][] = new Listener($this, $params, $callback);

        return $this;
    }

    /**
     * Инициирует событие
     * @param string $eventName имя события
     * @param mixed $params дополнительные параметры события
     * @param int|null $index индекс обработчика в массиве (стеке)
     * @return mixed результат, возвращаемый запущенным обработчиком
     * @throws EventBusException
     */
    public function trigger(string $eventName, &$params, ?int $index = null)
    {
        if(!isset($this->listeners[$eventName])) {
            throw new EventBusException("undefined event name {$eventName}");
        }
        /** @var Listener[] $listeners */
        $listeners = $this->listeners[$eventName];
        if($index === null) {
            $index = sizeof($listeners)-1;
        }
        if($index < 0 || !isset($listeners[$index])) {
            throw new EventBusException("undefined event name {$eventName}");
        }
        return $listeners[$index]->handle($params);
    }

    /**
     * Вызывает предыдущий обработчик события в стеке
     * @param array $listenerParams внутренные параметры обработчика
     * @param mixed $params дополнительные параметры события
     * @return mixed|null результат, возвращаемый запущенным обработчиком, либо null, если дошли до дна стека
     * @throws EventBusException
     */
    public function handlePrevious(array $listenerParams, &$params)
    {
        if($listenerParams['index']-1 < 0) {
            return null;
        }
        return $this->trigger($listenerParams['eventName'], $params, $listenerParams['index']-1);
    }
}