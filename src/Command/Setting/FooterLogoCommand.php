<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Entity\SystemSetting;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Class FooterLogoCommand
 */
class FooterLogoCommand extends Command
{
    public const FOOTER_LOGO_DISPLAY_FLG = [
        'on' => SystemSetting::FOOTER_LOGO_DISPLAY_FLG_ON,
        'off' => SystemSetting::FOOTER_LOGO_DISPLAY_FLG_OFF,
    ];

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);
        $parser->addOption('display', [
            'name' => 'display',
            'choices' => array_keys(static::FOOTER_LOGO_DISPLAY_FLG),
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        if (!$this->checkClientOption()) {
            $this->errorExit('FooterLogo Error: invalid client.');
        }

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        if (!isset(static::FOOTER_LOGO_DISPLAY_FLG[$args->getOption('display')])) {
            $this->errorExit('FooterLogo Error: invalid display.');
        }
        $footerLogoDisplayFlg = static::FOOTER_LOGO_DISPLAY_FLG[$args->getOption('display')];

        $systemSettingsTable->updateFooterLogoDisplayFlg($footerLogoDisplayFlg);

        $this->outputEndMessage();
    }
}
