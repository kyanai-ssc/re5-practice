<?php
declare(strict_types=1);

namespace App\Command;

use App\Utility\FileUtility;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class TmpDeleteCommand
 */
class TmpDeleteCommand extends Command
{
    public const TMP_FILE_DELETE_TIME = 24;

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

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        $nowTime = $this->commonData()->getNowDateTime();

        /** @var \App\Model\Table\AdminPassResetTokensTable $adminPassResetTokensTable */
        $adminPassResetTokensTable = $this->getTableLocator()->get('AdminPassResetTokens');
        $adminPassResetTokensTable->deleteAll(['expiration_timestamp <=' => $nowTime]);

        /** @var \App\Model\Table\OptinTokensTable $optinTokensTable */
        $optinTokensTable = $this->getTableLocator()->get('OptinTokens');
        $optinTokensTable->deleteAll(['expiration_timestamp <=' => $nowTime]);

        /** @var \App\Model\Table\UserPasswordReminderTokensTable $userPasswordReminderTokensTable */
        $userPasswordReminderTokensTable = $this->getTableLocator()->get('UserPasswordReminderTokens');
        $userPasswordReminderTokensTable->deleteAll(['expiration_timestamp <=' => $nowTime]);

        /** @var \App\Model\Table\WaitingCancellationConfTokensTable $waitingCancellationConfTokensTable */
        $waitingCancellationConfTokensTable = $this->getTableLocator()->get('WaitingCancellationConfTokens');
        $waitingCancellationConfTokensTable->deleteAll(['expiration_timestamp <=' => $nowTime]);

        /** @var \App\Model\Table\ReservationGuestCodesTable $reservationGuestCodesTable */
        $reservationGuestCodesTable = $this->getTableLocator()->get('ReservationGuestCodes');
        $reservationGuestCodesTable->deleteAll(['expiration_timestamp <=' => $nowTime]);

        /** @var \App\Model\Table\TempAccessSummariesTable $tempAccessSummariesTable */
        $tempAccessSummariesTable = $this->getTableLocator()->get('TempAccessSummaries');
        $tempAccessSummariesTable->deleteOldData();

        $deleteTime = $nowTime->subHours(static::TMP_FILE_DELETE_TIME);

        /** tmpファイルを削除 */
        FileUtility::tmpFileDeleteOverTime(TMP_UPLOAD_FILES, $deleteTime);

        /** アップロードのtmpファイルを削除 */
        FileUtility::tmpFileDeleteOverTime(TMP_UPLOAD_IMPORT, $deleteTime);

        /** @var \App\Model\Table\AppAccessTokensTable $appAccessTokensTable */
        $appAccessTokensTable = $this->getTableLocator()->get('AppAccessTokens');
        $appAccessTokensTable->deleteAll(['expiration_timestamp <=' => $nowTime]);

        $this->outputEndMessage();
    }
}
