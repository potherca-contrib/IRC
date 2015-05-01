<?php
namespace Buttress\IRC\Action;

use Buttress\IRC\Connection\ConnectionInterface;

interface ActionManagerInterface
{

    /**
     * Add an Action handler against a command
     *
     * @param string          $command [\*a-z] "*" for all
     * @param ActionInterface $action
     * @return void
     */
    public function add($command, ActionInterface $action);

    /**
     * Handle connection action
     *
     * @param ConnectionInterface $connection
     * @return mixed
     */
    public function handleConnect(ConnectionInterface $connection);

    /**
     * @param ConnectionInterface $connection
     * @param string              $string
     * @return mixed
     */
    public function handleRaw(ConnectionInterface $connection, $string);

    public function handleTick(ConnectionInterface $connection);

}