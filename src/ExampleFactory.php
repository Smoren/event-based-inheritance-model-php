<?php


namespace Smoren\EventBus;


use stdClass;

class ExampleFactory
{
    /**
     * @throws EventBusException
     */
    public static function first()
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
}