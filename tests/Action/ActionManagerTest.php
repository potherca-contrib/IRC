<?php

use Buttress\IRC\Action\ActionManager;
use Buttress\IRC\Connection\Connection;
use Buttress\IRC\Message\MessageFactory;

class ActionManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testHandleConnect()
    {
        $mock = $this->getMock('\Buttress\IRC\Action\ActionInterface');
        $mock->expects($this->once())->method('handleConnect');

        $factory = new MessageFactory();
        $manager = new ActionManager($factory, $mock);

        $manager->handleConnect(new Connection($manager, ''));
    }

    public function testHandleTick()
    {
        $ping_action = new \Buttress\IRC\Action\PingAction();

        $mock = $this->getMock('\Buttress\IRC\Action\ActionInterface');
        $mock->expects($this->once())
            ->method('handleTick')
            ->with($this->isInstanceOf('\Buttress\IRC\Connection\ConnectionInterface'));


        $factory = new MessageFactory();
        $manager = new ActionManager($factory, $ping_action);
        $manager->add('tick', $mock);
        $manager->add('tick', $ping_action);

        $manager->handleTick(new Connection($manager, ''));
    }

}
