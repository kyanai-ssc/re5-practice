<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * Postcode Entity
 *
 * @property int $id
 * @property string $zip_code
 * @property string $state_id
 * @property string $state
 * @property string $state_kana
 * @property string $city
 * @property string $city_kana
 * @property string $address
 * @property string $address_kana
 * @property string $company
 * @property string $company_kana
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 */
class Postcode extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'zip_code' => true,
        'state_id' => true,
        'state' => true,
        'state_kana' => true,
        'city' => true,
        'city_kana' => true,
        'address' => true,
        'address_kana' => true,
        'company' => true,
        'company_kana' => true,
        'created' => false,
        'modified' => false,
    ];
}
