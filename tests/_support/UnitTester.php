<?php

use Psr\Container\ContainerInterface;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class UnitTester extends \Codeception\Actor
{
    use _generated\UnitTesterActions;

    /**
     * Get the container
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        static $container = null;
        if ($container === null) {
            $app = require __DIR__ . '/../../config/bootstrap.php';

            $container = $app->getContainer();
        }

        return $container;
    }
}
