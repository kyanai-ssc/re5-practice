<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class MailCommand
 *
 * @package App\Command
 */
class MailCommand extends Command
{
    public const SLEEP_MICRO_SECONDS = 250000;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->setTranslate();
    }

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->fetchTable('MailDeliveries');

        $this->addClientOption($parser);
        $parser->addArgument('id', [
            'help' => 'Send Mail Id',
            'require' => false,
            'choices' => array_map(
                'strval',
                $mailDeliveriesTable->find('send')->all()->combine('id', 'id')->toArray()
            ),
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->fetchTable('MailDeliveries');

        $id = $args->getArgument('id');
        if (!is_null($id)) {
            if (!$mailDeliveriesTable->validatePrimaryKey($id)) {
                $this->errorExit('Invalid id.');
            }
            $id = (int)$id;
        }

        $mailDeliveriesTable->sendMail($id, [
            'onStart' => function ($entity) {
                $this->outputMessage('Mail Send Start: ' . $entity->get('id'));
            },
            'onEnd' => function ($entity) {
                $this->outputMessage('Mail Send End: ' . $entity->get('id'));
            },
        ]);

        usleep(static::SLEEP_MICRO_SECONDS);

        $this->outputEndMessage();
    }
}
