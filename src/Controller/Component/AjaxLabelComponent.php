<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Form\Admin\Labels\LabelForm as AdminLabelForm;
use App\Form\User\Labels\LabelForm as UserLabelForm;
use App\Utility\CommonData\CommonDataTrait;
use Cake\Controller\Component;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;

/**
 * ラベル取得コンポーネント
 */
class AjaxLabelComponent extends Component
{
    use CommonDataTrait;
    use LocatorAwareTrait;

    /**
     * @var bool
     */
    protected $public = false;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->public = Hash::get($config, 'public', false);
    }

    /**
     * Parent method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function parent()
    {
        if ($this->public) {
            $labelForm = new UserLabelForm();
        } else {
            $labelForm = new AdminLabelForm();
        }

        $labelInputs = (array)$this->getController()->getRequest()->getData();
        $this->getController()->getRequest()->withParsedBody($labelInputs);

        $checkResult = $labelForm->execute($labelInputs);
        $labelLists = [];

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $formType = $labelTable->setAjaxForm($labelInputs['label_select_type']);

        if ($checkResult) {
            $labelInputData = $labelForm->getData();
            $labelInputs['id'] = $labelInputData['id'];

            $labelLists = $labelTable->getParentLabel(
                $labelInputs['id'],
                Hash::get($labelInputs, 'exclude_id'),
                $this->public,
                $formType['max_depth'],
                $this->commonData()->getUserLabelId()
            );
        }

        $this->getController()->set('checkResult', $checkResult);
        $this->getController()->set('excludeId', Hash::get($labelInputs, 'exclude_id'));
        $this->getController()->set(compact('labelLists'));
        $this->getController()->set(compact('labelInputs'));
        $this->getController()->set(compact('formType'));
        $this->getController()->set('selected', '');
    }
}
