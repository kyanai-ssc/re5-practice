<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Command\Traits\CommandTrait;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\FileUtility;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Form\Form;
use Cake\Utility\Hash;

/**
 * インポート用コンポーネント
 */
class ImportComponent extends Component
{
    use CommandTrait;
    use CommonDataTrait;

    /**
     * @var string
     */
    protected $modelName;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->modelName = $config['model'];
    }

    /**
     * CSVインポート処理
     *
     * @param \Cake\Form\Form $form フォーム
     * @param bool $modify 更新を実施するか
     * @return void
     */
    public function importData(Form $form, $modify = true)
    {
        $finish = false;
        $errorMessage = [];

        $inputs = (array)$this->getController()->getRequest()->getData();

        if ($form->execute($inputs)) {
            $finish = true;

            //tempにアップロード
            $importFile = FileUtility::tmpUploadFile(TMP_UPLOAD_IMPORT, $inputs['file']);
            $loginAdmin = $this->commonData()->getAdminLoginData();

            //importコマンド
            if (is_array($importFile)) {
                $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
                    'import',
                    $this->modelName,
                    $importFile['fileName'],
                    $loginAdmin['id'],
                    Configure::readOrFail('Setting.batch.client'),
                    '--quiet',
                ]);
            }
        } else {
            $errorMessage = Hash::flatten($form->getErrors());
        }

        $this->getController()->set([
            'finish' => $finish,
            'errorMessage' => $errorMessage,
        ]);
    }
}
