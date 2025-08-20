<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\FormItem;
use App\Model\InputType\InputTypeManagerFactory;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\ListOutputInterface;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use Cake\Core\Configure;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * InputTypeHelper class.
 *
 * @property \App\View\Helper\CommonDataHelper $CommonData
 * @property \Cake\View\Helper\FormHelper $Form
 */
class InputTypeHelper extends Helper
{
    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    public $helpers = ['Form', 'CommonData'];

    /**
     * すべての入力タイプを取得
     *
     * @return array 入力タイプ
     */
    public function getInputTypes()
    {
        return Configure::readOrFail('Master.form.inputType');
    }

    /**
     * 入力タイプマネージャを取得
     *
     * @param int $inputType 入力タイプ
     * @param bool|null $isAdmin 管理者側フラグ
     * @return \App\Model\InputType\AbstractInputTypeManager 入力タイプマネージャ
     */
    public function inputTypeManager(int $inputType, ?bool $isAdmin = null)
    {
        if (!isset($isAdmin)) {
            $isAdmin = $this->CommonData->existsAdminLoginData();
        }

        return InputTypeManagerFactory::getInstance($inputType, $isAdmin);
    }

    /**
     * フォーム項目詳細の入力項目を出力
     *
     * @param int $inputType 入力タイプ
     * @param bool $keepsInput 入力値保持
     * @return string
     */
    public function renderFormItemDetailTab(int $inputType, bool $keepsInput = true)
    {
        $manager = $this->inputTypeManager($inputType);
        if (!$manager->hasFormItemDetailTab()) {
            return '';
        }

        $template = 'InputType/FormItemDetails/' . $manager->getInputTypeName() . '/fieldset';
        $data = [
            'inputType' => $inputType,
        ];
        if (!$keepsInput) {
            $data['formItem'] = null;
        }

        $context = null;
        if (!$keepsInput) {
            $context = $this->Form->context();
            $this->Form->context(
                $this->Form->contextFactory()->get($this->getView()->getRequest()->withParsedBody([]), [])
            );
        }
        $output = $this->getView()->element($template, $data);
        if (isset($context)) {
            $this->Form->context($context);
        }

        return $output;
    }

    /**
     * 検索項目を出力
     *
     * @param \App\Model\Entity\FormItem $formItem フォーム項目
     * @return string
     */
    public function renderSearchItem(FormItem $formItem)
    {
        if (!($formItem->getInputTypeItem() instanceof SearchDisplayInterface)) {
            return '';
        }

        $manager = $this->inputTypeManager($formItem->get('input_type'));
        $template = 'InputType/Inputs/' . $manager->getInputTypeName() . '/search';
        $data = [
            'formItem' => $formItem,
        ];

        $output = $this->getView()->element($template, $data);

        return $output;
    }

    /**
     * 一覧項目を出力
     *
     * @param \App\Model\Entity\FormItem $formItem フォーム項目
     * @param array|null $options オプション引数
     * @return string
     */
    public function renderListItem(FormItem $formItem, ?array $options = null)
    {
        $inputTypeItem = $formItem->getInputTypeItem();
        if (!($inputTypeItem instanceof ListOutputInterface)) {
            return '';
        }

        $manager = $this->inputTypeManager($formItem->get('input_type'));
        $template = 'InputType/Inputs/' . $manager->getInputTypeName() . '/list';
        $data = [
            'formItem' => $formItem,
            'listValue' => $inputTypeItem->getListValue($options),
            'options' => $options,
        ];

        $output = $this->getView()->element($template, $data);

        return $output;
    }

    /**
     * 詳細項目を出力
     *
     * @param \App\Model\Entity\FormItem $formItem フォーム項目
     * @param array|null $options オプション引数
     * @return string
     */
    public function renderDetailItem(FormItem $formItem, ?array $options = null)
    {
        $manager = $this->inputTypeManager($formItem->get('input_type'));
        $template = 'InputType/Inputs/' . $manager->getInputTypeName() . '/detail';
        $data = [
            'formItem' => $formItem,
            'detailValue' => $formItem->getInputTypeItem()->getDetailValue($options),
            'options' => $options,
        ];

        $output = $this->getView()->element($template, $data);

        return $output;
    }

    /**
     * 入力項目を出力
     *
     * @param \App\Model\Entity\FormItem $formItem フォーム項目
     * @param array|null $options オプション引数
     * @return string|null
     */
    public function renderFieldsetItem(FormItem $formItem, ?array $options = null)
    {
        $inputTypeItem = $formItem->getInputTypeItem();
        if (!($inputTypeItem instanceof InputInterface) || !$inputTypeItem->canInput()) {
            return $this->renderDetailItem($formItem, $options);
        }

        $manager = $this->inputTypeManager($formItem->get('input_type'));
        $template = 'InputType/Inputs/' . $manager->getInputTypeName() . '/fieldset';
        $data = [
            'formItem' => $formItem,
            'options' => $options,
        ];

        $html = '{{frontWord}}<span class="input{{required}}error">{{content}}</span>{{backWord}}{{error}}';
        $inputTemplate = [
            'inputContainerError' => $html,
        ];

        $this->Form->setTemplates($inputTemplate);

        $output = $this->getView()->element($template, $data);

        return $output;
    }

    /**
     * 前後文言を出力
     *
     * @param \App\Model\Entity\FormItem $formItem フォーム項目
     * @param array $options オプション引数
     * @return array
     */
    public function renderFrontBackWord(FormItem $formItem, array $options = [])
    {
        $inputTypeItem = $formItem->getInputTypeItem();
        if (!($inputTypeItem instanceof InputInterface) || !$inputTypeItem->canInput()) {
            return [];
        }

        //取得する詳細のindex
        $index = Hash::get($options, 'index', 0);

        $template = 'Common/input_word';
        $output = [];
        if ($inputTypeItem->getFrontWord($index) !== '') {
            $output['frontWord'] = $this->getView()->element($template, [
                'word' => $inputTypeItem->getFrontWord($index),
            ]);
        }

        if ($inputTypeItem->getBackWord($index) !== '') {
            $output['backWord'] = $this->getView()->element($template, [
                'word' => $inputTypeItem->getBackWord($index),
            ]);
        }

        return $output;
    }

    /**
     * テンプレートエラーの設定を取得
     *
     * @param \App\Model\Entity\FormItem $formItem フォーム項目
     * @return mixed
     */
    public function renderErrorTemplates(FormItem $formItem)
    {
        $templates = [];
        if (
            $formItem->get('input_type') === FormItem::INPUT_TYPE_MULTI_TEXTBOX
            || $formItem->get('input_type') === FormItem::INPUT_TYPE_FULL_NAME
            || $formItem->get('input_type') === FormItem::INPUT_TYPE_PHONE_NUMBER
        ) {
            $html = '{{frontWord}}<span class="input{{required}}error">{{content}}{{error}}</span>{{backWord}}';
            $templates['inputContainerError'] = $html;
        }

        return $templates;
    }
}
