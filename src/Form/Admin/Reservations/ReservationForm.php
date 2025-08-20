<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Common\Reservations\ReservationForm as CommonReservationForm;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\Table\ReservationsTable;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Form\Schema;

/**
 * 予約フォーム
 */
class ReservationForm extends CommonReservationForm
{
    use ContinuousTrait;

    /**
     * @var bool
     */
    protected $adminFlg = true;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = parent::_buildSchema($schema);
        $schema
            ->addField('reservations.user_id', 'string')
            ->addField('reservations.charge', 'string')
            ->addField('reservations.calculate_charge', 'string')
            ->addField('reservations.reservation_status_id', 'string')
            ->addField('reservations.payment_method_id', 'string')
            ->addField('reservations.payment_status_id', 'string')
            ->addField('reservations.reception_status_id', 'string')
            ->addField('reservations.payment_tracking_id', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function getReservationFormGroups(?int $formType = null)
    {
        if (!isset($this->reservationFormGroups)) {
            // $this->reservationFormGroupsにarrayが格納される
            parent::getReservationFormGroups($formType);

            if (isset($this->reservationFormGroups)) {
                /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
                $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

                $userAuthorityId = $this->getReservationParameter('user_authority_id');
                if (((string)$userAuthorityId) !== ((string)$userAuthoritiesTable->getGuestAuthority()->get('id'))) {
                    $reservationType = $this->getReservationParameter('reservation_type');
                    if (
                        ((string)$reservationType) === ((string)ReservationsTable::RESERVATION_TYPE_EXISTING_USER)
                        || $this->reservationEntity instanceof EntityInterface
                    ) {
                        // @phpstan-ignore-next-line
                        if (!isset($this->reservationFormGroups[FormGroup::FORM_TYPE_USER])) {
                            throw new CakeException();
                        }
                        $this->reservationFormGroups[FormGroup::FORM_TYPE_USER] = $this->getSearchItemUserForm(
                            $this->reservationFormGroups[FormGroup::FORM_TYPE_USER]
                        );
                    }
                }
            }
        }

        return parent::getReservationFormGroups($formType);
    }

    /**
     * 会員検索のフォーム項目を取得
     *
     * @param array $formGroups フォーム項目
     * @return array
     */
    protected function getSearchItemUserForm($formGroups)
    {
        /** @var \App\Model\Table\AdminSearchItemsTable $adminSearchItemsTable */
        $adminSearchItemsTable = $this->getTableLocator()->get('AdminSearchItems');

        $searchItems = $adminSearchItemsTable->find('formItem', [
            'inputs' => ['type' => AdminSearchItem::TYPE_RESERVATION_USER],
        ])->first();

        if (is_array($searchItems)) {
            $formItemIds = [];
            foreach ($searchItems as $items) {
                foreach ($items as $item) {
                    if ($item instanceof FormItem) {
                        $formItemIds[$item->get('id')] = $item->get('id');
                    }
                }
            }
        }

        $result = [];
        foreach ($formGroups as $formGroup) {
            $formGroup = clone $formGroup;

            $formItems = [];
            foreach ((array)$formGroup->get('form_items') as $formItem) {
                if (isset($formItemIds[$formItem->get('id')])) {
                    $formItems[] = $formItem;
                } elseif (!is_array($searchItems)) {
                    //設定がない場合は全表示
                    $formItems[] = $formItem;
                }
            }

            if (!empty($formItems)) {
                $formGroup->set('form_items', $formItems);
                $formGroup->clean();

                $result[] = $formGroup;
            }
        }

        return $result;
    }
}
