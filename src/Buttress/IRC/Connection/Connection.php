<?php
namespace Buttress\IRC\Connection;

use Buttress\IRC\Action\ActionManagerInterface;
use Buttress\IRC\Message\MessageInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Connection implements ConnectionInterface
{

    protected $connected = false;

    /**
     * @type resource
     */
    protected $socket;
    protected $server;
    protected $port;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ActionManagerInterface
     */
    protected $manager;

    public function __construct(ActionManagerInterface $manager, $server, $port = 6667)
    {
        $this->server = $server;
        $this->port = $port;
        $this->manager = $manager;
    }

    public function connect()
    {
        if ($this->isConnected()) {
            return false;
        }

        $this->log("Connecting to {$this->server} : {$this->port}");

        $this->connected = true;
        $this->connectionLoop();

        return true;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return ($this->connected && is_resource($this->socket));
    }

    public function log($message, array $context = array(), $level = LogLevel::NOTICE)
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    protected function connectionLoop()
    {
        $socket = $this->getSocket();
        if (!$socket) {
            $this->socket = $socket = fsockopen($this->server, $this->port);
            $this->manager->handleConnect($this);
            socket_set_blocking($this->socket, false);
        }

        while ($this->isConnected() && !feof($socket)) {
            $raw = fgets($socket);

            if ($raw) {
                $this->handleRaw($raw);
            }

            $this->handleTick();

            if ($raw === false) {
                // no data, slow down so we don't burn cpu cycles
                usleep(10000);
            }
        }

        $this->disconnect();
    }

    /**
     * @return resource
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param resource $socket
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
    }

    /**
     * @param string $raw
     */
    public function handleRaw($raw)
    {
        $this->log($raw, array(), 'debug');
        $this->manager->handleRaw($this, $raw);
    }

    public function handleTick()
    {
        $this->manager->handleTick($this);
    }

    /**
     * @return boolean|null
     */
    public function disconnect()
    {
        if ($this->connected && $socket = $this->getSocket()) {
            fclose($socket);
        }

        $this->log('Disconnected.');

        $this->socket = null;
        $this->connected = false;
    }

    /**
     * @param MessageInterface $message
     * @return boolean|null
     */
    public function sendMessage(MessageInterface $message)
    {
        $this->sendRaw($message->getRaw());
    }

    /**
     * @param string $raw
     * @return boolean|null
     */
    public function sendRaw($raw)
    {
        $this->log("Sending {$raw}");
        fwrite($this->getSocket(), $raw . PHP_EOL);
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

}
