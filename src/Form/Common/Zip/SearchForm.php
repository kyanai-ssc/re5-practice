<?php
declare(strict_types=1);

namespace App\Form\Common\Zip;

use App\Form\AppForm;
use App\Form\Common\CommonFormTrait;
use App\Locale\Message;
use App\Utility\Zip\ZipSearch;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 住所検索フォーム
 */
class SearchForm extends AppForm
{
    use CommonFormTrait;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('zip', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('zip', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('zip', __(Message::ERROR_NOT_EMPTY), false)
            ->add('zip', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'custom' => [
                    'rule' => ['custom', '/^[0-9]{3}-?[0-9]{4}$/'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        return $validator;
    }

    /**
     * エラーメッセージを文字列で取得
     *
     * @return string エラーメッセージ
     */
    public function getErrorText()
    {
        return implode("\n", Hash::flatten($this->getErrors()));
    }

    /**
     * 住所データを検索
     *
     * @param string $zipCode 郵便番号
     * @return array 住所データ
     */
    public function searchAddress($zipCode)
    {
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->getTableLocator()->get('Prefectures');

        $zipCode = $this->filterZipCode($zipCode);
        if (Configure::check('Env.zipSearch.options')) {
            $result = $this->searchAddressByApi($zipCode);
        } else {
            $result = $this->searchAddressByDb($zipCode);
        }

        $prefectureIds = Hash::combine($prefecturesTable->getData(), '{*}.code', '{*}.id');
        foreach ($result['data'] as $index => $address) {
            $prefectureCode = preg_replace('/^0/', '', $address['state_id']);
            if (!is_scalar($prefectureCode)) {
                throw new CakeException();
            }
            if (isset($prefectureIds[$prefectureCode])) {
                $result['data'][$index] = [
                    'zip' => $zipCode,
                    'prefecture' => $prefectureIds[$prefectureCode],
                    'prefectureName' => $address['state'],
                    'municipality' => $address['city'],
                    'town' => $address['address'],
                ];
            } else {
                unset($result['data'][$index]);
            }
        }

        return $result;
    }

    /**
     * APIで住所データを検索
     *
     * @param string $zipCode 郵便番号
     * @return array 住所データ
     */
    protected function searchAddressByApi($zipCode)
    {
        $messages = [
            ZipSearch::ERROR_EMPTY => Message::ERROR_NOT_EMPTY,
            ZipSearch::ERROR_INVALID => Message::ERROR_INVALID_VALUE,
            ZipSearch::ERROR_NOT_EXISTS => Message::ERROR_NOT_EXISTS,
        ];

        $zipSearch = new ZipSearch(Configure::readOrFail('Env.zipSearch.options'));
        $result = $zipSearch->searchAddress($zipCode);
        if (isset($result['error'])) {
            $result['error'] = $messages[$result['error']];
        }

        return $result;
    }

    /**
     * DBから住所データを検索
     *
     * @param string $zipCode 郵便番号
     * @return array 住所データ
     */
    protected function searchAddressByDb($zipCode)
    {
        /** @var \App\Model\Table\PostcodesTable $postcodesTable */
        $postcodesTable = $this->getTableLocator()->get('Postcodes');

        $data = $postcodesTable->find('searchAddress', [
            'inputs' => [
                'zip_code' => $zipCode,
            ],
        ])->toArray();
        if (empty($data)) {
            return [
                'data' => [],
                'error' => Message::ERROR_NOT_EXISTS,
            ];
        }

        return [
            'data' => $data,
            'error' => null,
        ];
    }

    /**
     * 郵便番号をフィルタリング
     *
     * @param mixed $value 値
     * @return mixed フィルタリング済みの値
     */
    protected function filterZipCode($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        if (preg_match('/^[0-9]{3}-[0-9]{4}$/', $value) === 1) {
            $value = preg_replace('/-/', '', $value);
        }

        return $value;
    }
}
