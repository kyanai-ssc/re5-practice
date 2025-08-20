<?php
declare(strict_types=1);

namespace App\Command\Setting;

use App\Command\Command;
use App\Model\Table\SiteSettingsTable;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Utility\Inflector;
use Cake\View\CellTrait;

/**
 * Class UpdateCustomCssCommand
 */
class UpdateCustomCssCommand extends Command
{
    use CellTrait;

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
        $parser->addOption('site_id', [
            'name' => 'site_id',
            'default' => SiteSettingsTable::BASE_ID,
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        $siteId = $args->getOption('site_id');
        if (is_numeric($siteId)) {
            $cms = $siteSettingsTable->get($siteSettingsTable->getId((int)$siteId), [
                'finder' => 'cms',
            ]);

            $siteSettingsTable->updateCustomCss($cms, $this->_createCell());
        } else {
            $this->errorExit('UpdateCustomCss Error: invalid siteId.');
        }

        $this->outputEndMessage();
    }

    /**
     * @return \Cake\View\Cell
     */
    private function _createCell()
    {
        $className = 'App\View\Cell\CssCell';
        $options = [
            'action' => 'display',
            'args' => [],
        ];
        /** @var \App\View\Cell\CssCell $instance */
        $instance = new $className(new ServerRequest(), new Response(), null, $options);
        $action = 'display';

        $builder = $instance->viewBuilder();
        $builder->setTemplate(Inflector::underscore($action));

        return $instance;
    }
}
