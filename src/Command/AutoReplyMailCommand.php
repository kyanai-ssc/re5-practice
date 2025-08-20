<?php
declare(strict_types=1);

namespace App\Command;

use App\Form\Console\AutoReplyMails\ResendForm;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Hash;

/**
 * Class AutoReplyMailCommand
 */
class AutoReplyMailCommand extends Command
{
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

        $this->addClientOption($parser);
        $parser->addOption('id', [
            'name' => 'id',
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        /** @var \App\Model\Table\AutoReplyMailHistoriesTable $autoReplyMailHistoriesTable */
        $autoReplyMailHistoriesTable = $this->getTableLocator()->get('AutoReplyMailHistories');

        $id = array_unique((array)preg_split('/,/', (string)$args->getOption('id')));
        $inputs = [
            'id' => $id,
        ];

        $resendForm = new ResendForm();
        if (!$resendForm->execute($inputs)) {
            foreach ($resendForm->getErrors() as $name => $errors) {
                foreach (Hash::flatten($errors) as $error) {
                    $this->outputErrorMessage('AutoReplyMail Error: ' . $name . ':' . $error);
                }
            }
            $this->errorExit();
        }

        $autoReplyMailHistories = $autoReplyMailHistoriesTable->find('resend', [
            'inputs' => $resendForm->getData(),
        ]);
        foreach ($autoReplyMailHistories as $autoReplyMailHistory) {
            if (is_null($autoReplyMailHistory->get('data'))) {
                $this->outputErrorMessage(
                    'AutoReplyMail Error ID:' . $autoReplyMailHistory->get('id') . ' data is null.'
                );
            } elseif ($autoReplyMailHistory->isSent()) {
                $this->outputErrorMessage(
                    'AutoReplyMail Error ID:' . $autoReplyMailHistory->get('id') . ' already sent.'
                );
            } else {
                $this->outputMessage('AutoReplyMail Send Start ID:' . $autoReplyMailHistory->get('id'));
                $autoReplyMailHistoriesTable->resendAutoReplyMail($autoReplyMailHistory);
                $this->outputMessage('AutoReplyMail Send End ID:' . $autoReplyMailHistory->get('id'));
            }
        }

        $this->outputEndMessage();
    }
}
