<?php


namespace Smoren\EventBus;


use stdClass;

class EventBus
{
    /**
     * @var Listener[][]
     */
    protected $listeners = [];

    /**
     * @throws EventBusException
     */
    public static function example()
    {
        $bus = new EventBus([
            'onTest' => function(&$params, Listener $listener) {
                $params['a']++;
                echo "l1: ".json_encode($params)."\n";
            },
        ]);

        $bus->addListener('onTest', function(&$params, Listener $listener) {
            $listener->handlePrevious($params);
            $params['a']++;
            echo "l2: ".json_encode($params)."\n";
        });
        $bus->addListener('onTest', function(&$params, Listener $listener) {
            $listener->handlePrevious($params);
            $params['a']++;
            echo "l3: ".json_encode($params)."\n";
            return array_merge($params, ['b' => 0]);
        });

        $bus->addListener('onTest1', function(&$params, Listener $listener) {
            $listener->handlePrevious($params);
            $params->a++;
            echo "ll1: ".json_encode($params)."\n";
        });
        $bus->addListener('onTest1', function(&$params, Listener $listener) {
            $listener->handlePrevious($params);
            $params->a++;
            echo "ll2: ".json_encode($params)."\n";
            return $params;
        });

        $params = ['a' => 1];
        $result = $bus->trigger('onTest', $params);
        print_r($result);
        echo "\n";

        $params = new stdClass();
        $params->a = 1;
        $result = $bus->trigger('onTest1', $params);
        print_r($result);
    }

    /**
     * EventBus constructor.
     * @param array $defaultListeners
     */
    public function __construct(array $defaultListeners = [])
    {
        /**
         * @var string $eventName
         * @var Listener $callback
         */
        foreach($defaultListeners as $eventName => $callback) {
            $this->addListener($eventName, $callback);
        }
    }

    /**
     * @param string $eventName
     * @param callable $callback
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
     * @param string $eventName
     * @param mixed $params
     * @param int|null $index
     * @return mixed
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
     * @param array $listenerParams
     * @param mixed $params
     * @return mixed|null
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