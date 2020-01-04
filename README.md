# Event-based inheritance model
Реализует следующую событийную модель:
- на каждое событие можно добавить неограниченное количество обработчиков, которые помещаются в стек данного события;
- при инициации события запускается только верхний обработчик из стека, однако он имеет доступ к предыдущему обработчику 
и может его запустить, если это необходимо.

Таким образом реализуется модель "цепочечного наследования", то есть каждый следующий обработчик может запустить 
предыдущий плюс доделать что-то свое, причем выполнить эти два действия в любом порядке.

### Демонстрация принципа работы:

```php
<?php

use Smoren\EventBasedInheritanceModel\EventBus;
use Smoren\EventBasedInheritanceModel\Listener;

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
    echo "onMyTest (1): ".json_encode($params)."\n";
    return $params;
});

// инициируем событие onTest
$params = ['a' => 1];
$result = $bus->trigger('onTest', $params);
print_r($result); // в результате получим то, что вернет верхний в стеке обработчик
/*
onTest (0): {"a":2}
onTest (1): {"a":3}
onTest (2): {"a":4}
Array
(
    [a] => 4
    [b] => 0
)
*/

echo "\n";

// инициируем событие onMyTest
$params = new \stdClass();
$params->a = 1;
$result = $bus->trigger('onMyTest', $params);
print_r($result); // в результате получим то, что вернет верхний в стеке обработчик
/*
onMyTest (0): {"a":2}
onMyTest (1): {"a":3}
stdClass Object
(
    [a] => 3
)
*/
```