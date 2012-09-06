<?php
namespace Command;

use Event\Listener;

/**
 * @copyright 2012
 * @author Anton Tokovoy <barss.dev@gmail.com>
 */
abstract class CommandManager implements Listener
{
    /**
     * @var CommandPool
     */
    protected $commandPool;

    public function __construct()
    {
        $this->commandPool = new CommandPool();
    }

    protected function isValid(CommandInterface $command)
    {
        return true;
    }

    public function processCommands(\Player $player, \Registry $registry)
    {
        try {
            $commandDef = $player->receiveCommandDef();
        } catch (\Exception\TransportException $e) {
            print "Player sent nothing\n";
            return false;
        }
        $command = Command\SimpleCommand::create($commandDef);
        if (!$this->isValid($command)) {
            return false;
        }

        //print sprintf("Received %s command\n", get_class($command));
        $command->execute($player, $registry);
    }

    public function sendCommands(\Player $player)
    {
        $commands = $this->commandPool->findAllByPlayer($player->getId());
        foreach ($commands as $command) {
            //print sprintf("Send %s command\n", get_class($command));
            /**
             * @var $command \Command\CommandInterface
             */
            $data = $command->toString();
            $player->sendCommandDef($data);
        }
    }

    public function flushCommands()
    {
        $this->commandPool->flush();
    }
}
