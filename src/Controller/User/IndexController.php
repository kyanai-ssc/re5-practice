<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Utility\Hash;

/**
 * Index Controller
 */
class IndexController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');

        $label = $this->getRequest()->getQuery('label', null);
        if (!is_null($label)) {
            if (is_string($label) && $labelsTable->hasLabelForUser($label)) {
                $this->setLabel($label);
            } else {
                throw new NotFoundException(Message::ERROR_NOT_FOUND);
            }
        } else {
            $this->resetLabel();
        }

        $this->isLoginRequire(['index']);

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->fetchTable('TagGroups');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        $siteSetting = $siteSettingsTable->getData();

        $formType = [];
        if ($siteSetting->get('top_search_label_flg')) {
            $formType = $labelsTable->setAjaxForm(Configure::readOrFail('Master.label.type.other'));
        }

        $tagList = [];
        if ($siteSetting->get('top_search_tag_flg')) {
            $tagList = $tagGroupsTable->getTagsList(true);
        }

        $eventNameList = [];
        if ($siteSetting->get('top_search_event_name_flg')) {
            $eventNameList = $eventsTable->getEventNameForPublic(true);
        }

        $newsList = [];
        if ($this->Authority->checkAuthority('News', 'list')) {
            $newsList = $this->fetchTable('News')->find('publicList', ['top' => true]);
        }

        if ($this->getRequest()->is('post')) {
            $inputData = $this->getRequest()->getData();

            $label = Hash::get((array)$inputData, 'label_id');
            $tag = Hash::get((array)$inputData, 'tag_id', []);
            $eventName = Hash::get((array)$inputData, 'event_name');

            return $this->redirect([
                'prefix' => 'User',
                'controller' => 'Reservations',
                'action' => 'calendar',
                '?' => [
                    'label_id' => $label,
                    'tag_id' => $tag,
                    'event_name' => $eventName,
                ],
            ]);
        }

        // ビュー変数
        $this->enableLoginRedirectBack();
        $this->set([
            'tagList' => $tagList,
            'newsList' => $newsList,
            'formType' => $formType,
            'eventNameList' => $eventNameList,
        ]);
    }
}
