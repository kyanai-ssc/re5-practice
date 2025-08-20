<?php
declare(strict_types=1);

namespace App\Controller\User\Ajax;

use App\Controller\Traits\AjaxReservationsTrait;
use App\Controller\Traits\AjaxTrait;
use App\Controller\User\ReservationsController as BaseReservationsController;
use App\Controller\UserAppController;
use App\Form\User\Reservations\CalendarForm;
use App\Form\User\Reservations\CalendarPopupForm;
use App\Form\User\Reservations\OptinForm;
use App\Form\User\Reservations\ReservationForm;
use App\Model\Table\ReservationsTable;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;

/**
 * Reservations Controller
 */
class ReservationsController extends UserAppController
{
    use AjaxReservationsTrait;
    use AjaxTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'calendar',
            'calendarPage',
            'calendarPopup',
            'changeForm',
        ]);
        $this->FormProtection->setConfig('unlockedActions', [
            'calendar',
            'calendarPage',
            'calendarPopup',
            'changeForm',
            'setContinuous',
            'viewContinuous',
            'removeContinuous',
            'removeAllContinuous',
        ]);

        return $response;
    }

    /**
     * Calendar method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendar()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->fetchTable('TagGroups');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        $sessionKey = 'reservations.calendar.search';
        $selectCalendar = false;
        $calendarFrame = null;
        if (ArrayUtility::inArray($this->getRequest()->getQuery('frame'), BaseReservationsController::CALENDAR_FRAME)) {
            $sessionKey = 'reservations.calendar.frame';
            $calendarFrame = $this->getRequest()->getQuery('frame');
        } elseif (is_scalar($this->getRequest()->getQuery('select_calendar'))) {
            $sessionKey = 'reservations.calendar.select';
            if (is_scalar($this->getRequest()->getQuery('edit_reservation_id'))) {
                $sessionKey = 'reservations.calendar.selectEdit';
            }
            $selectCalendar = true;
        }

        $calendarForm = new CalendarForm();

        $this->calendarAction($sessionKey, $selectCalendar, $calendarForm, false, true);

        // 検索項目
        $siteSetting = $siteSettingsTable->getData();
        $formType = [];
        if ($siteSetting->get('calendar_search_label_flg')) {
            $formType = $labelsTable->setAjaxForm(Configure::readOrFail('Master.label.type.other'));
        }
        $tagList = [];
        if ($siteSetting->get('calendar_search_tag_flg')) {
            $tagList = $tagGroupsTable->getTagsList(true);
        }
        $eventNameList = [];
        if ($siteSetting->get('calendar_search_event_name_flg')) {
            $eventNameList = $eventsTable->getEventNameForPublic(true);
        }

        $this->set([
            'calendarFrame' => $calendarFrame,
            'formType' => $formType,
            'tagList' => $tagList,
            'eventNameList' => $eventNameList,
        ]);
    }

    /**
     * Calendar page method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendarPage()
    {
        $sessionKey = 'reservations.calendar.search';
        $selectCalendar = false;
        $calendarFrame = null;
        if (ArrayUtility::inArray($this->getRequest()->getQuery('frame'), BaseReservationsController::CALENDAR_FRAME)) {
            $sessionKey = 'reservations.calendar.frame';
            $calendarFrame = $this->getRequest()->getQuery('frame');
        } elseif (is_scalar($this->getRequest()->getQuery('select_calendar'))) {
            $sessionKey = 'reservations.calendar.select';
            if (is_scalar($this->getRequest()->getQuery('edit_reservation_id'))) {
                $sessionKey = 'reservations.calendar.selectEdit';
            }
            $selectCalendar = true;
        }

        $calendarForm = new CalendarForm();

        $this->calendarAction($sessionKey, $selectCalendar, $calendarForm, false, false);

        $this->set([
            'calendarFrame' => $calendarFrame,
        ]);
    }

    /**
     * CalendarPopup method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function calendarPopup()
    {
        $sessionKey = 'reservations.calendar.search';
        $selectCalendar = false;
        $calendarFrame = null;
        if (ArrayUtility::inArray($this->getRequest()->getQuery('frame'), BaseReservationsController::CALENDAR_FRAME)) {
            $sessionKey = 'reservations.calendar.frame';
            $calendarFrame = $this->getRequest()->getQuery('frame');
        } elseif (is_scalar($this->getRequest()->getQuery('select_calendar'))) {
            $sessionKey = 'reservations.calendar.select';
            if (is_scalar($this->getRequest()->getQuery('edit_reservation_id'))) {
                $sessionKey = 'reservations.calendar.selectEdit';
            }
            $selectCalendar = true;
        }

        $calendarPopupForm = new CalendarPopupForm();

        $this->calendarPopupAction($sessionKey, $selectCalendar, $calendarPopupForm);

        $this->set([
            'calendarFrame' => $calendarFrame,
        ]);
    }

    /**
     * ChangeForm method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function changeForm()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $mode = $this->getRequest()->getQuery('mode');
        $optinData = [];
        if (
            $siteSettingsTable->getData()->isUseFlgOn('optin_flg')
            && !$this->commonData()->existsUserLoginData()
            && $mode === 'add'
        ) {
            $optinForm = new OptinForm();
            if (!$this->getRequest()->getSession()->check('reservationOptin')) {
                throw new BadRequestException();
            }
            if (!$optinForm->execute((array)$this->getRequest()->getSession()->read('reservationOptin.optinToken'))) {
                throw new BadRequestException();
            }
            $optinData = (array)$this->getRequest()->getSession()->read('reservationOptin.optinData');
        }

        // 「ログインして予約」のタイプで未ログインの場合
        $reservationType = $this->getRequest()->getQuery('reservation_type');
        if (
            is_scalar($reservationType)
            && (string)$reservationType === (string)ReservationsTable::RESERVATION_TYPE_EXISTING_USER
            && !$this->commonData()->existsUserLoginData()
        ) {
            throw new BadRequestException();
        }

        $reservationForm = new ReservationForm();
        $reservationForm->setOptinData($optinData);

        $this->changeFormAction(
            $reservationForm,
            BaseReservationsController::TOKEN_VALIDATION_ADD,
            BaseReservationsController::TOKEN_VALIDATION_EDIT
        );
    }

    /**
     * SetContinuous method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function setContinuous()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('reservation_continuous_flg')) {
            throw new BadRequestException();
        }

        $this->setContinuousAction();
    }

    /**
     * ViewContinuous method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function viewContinuous()
    {
        $reservationForm = new ReservationForm();

        $this->viewContinuousAction($reservationForm);
    }

    /**
     * RemoveContinuous method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function removeContinuous()
    {
        $this->removeContinuousAction();
    }

    /**
     * RemoveAllContinuous method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function removeAllContinuous()
    {
        $this->removeAllContinuousAction();
    }
}
