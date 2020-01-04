<?php


namespace Smoren\EventBasedInheritanceModel;


use stdClass;

/**
 * Фабрика, демонстрирующая принцип работы шины событий с цепочечно-наследуемыми обработчиками
 * @package Smoren\EventBasedInheritanceModel
 * @author Smoren <ofigate@gmail.com>
 */
class ExampleFactory
{
    /**
     * @throws EventBusException
     */
    public static function first()
    {
        // создаем шину
        $bus = new EventBus([
            // вводим событие onTest и назначаем на него обработчик
            'onTest' => function(&$params, Listener $listener) {
                $params['a']++;
                echo "onTest (0): ".json_encode($params)."\n";
            },
        ]);

        // добавляем в стек обработчиков события onTest еще один обработчик
        $bus->addListener('onTest', function(&$params, Listener $listener) {
            $listener->handlePrevious($params); // в начале своего исполнения он запускает предыдущий
            $params['a']++;
            echo "onTest (1): ".json_encode($params)."\n";
        });

        // добавляем в стек обработчиков события onTest еще один обработчик
        $bus->addListener('onTest', function(&$params, Listener $listener) {
            $listener->handlePrevious($params); // в начале своего исполнения он запускает предыдущий
            $params['a']++;
            echo "onTest (2): ".json_encode($params)."\n";
            return array_merge($params, ['b' => 0]);
        });

        // вводим событие onTest1 и назначаем на него обработчик
        $bus->addListener('onMyTest', function(&$params, Listener $listener) {
            // в начале своего исполнения он запускает предыдущий, но предыдущего нет, поэтому строка не имеет смысла
            $listener->handlePrevious($params);
            $params->a++;
            echo "onMyTest (0): ".json_encode($params)."\n";
        });
        // добавляем в стек обработчиков события onTest1 еще один обработчик
        $bus->addListener('onMyTest', function(&$params, Listener $listener) {
            $listener->handlePrevious($params);
            $params->a++;
            echo "onMyTest (0): ".json_encode($params)."\n";
            return $params;
        });

        // инициируем событие onTest
        $params = ['a' => 1];
        $result = $bus->trigger('onTest', $params);
        print_r($result); // в результате получим то, что вернет верхний в стеке обработчик
        echo "\n";

        // инициируем событие onMyTest
        $params = new stdClass();
        $params->a = 1;
        $result = $bus->trigger('onMyTest', $params);
        print_r($result); // в результате получим то, что вернет верхний в стеке обработчик
    }
}