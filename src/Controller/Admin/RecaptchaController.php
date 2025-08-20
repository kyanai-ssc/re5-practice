<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use App\Model\Entity\RecaptchaSetting;
use Cake\Core\Exception\CakeException;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * Recaptcha Controller
 */
class RecaptchaController extends AdminAppController
{
    /**
     * recaptcha method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\RecaptchaSettingsTable $recaptchaSettingsTable */
        $recaptchaSettingsTable = $this->fetchTable('RecaptchaSettings');

        $recaptchaSetting = $recaptchaSettingsTable->find('default')->first();

        if (!$recaptchaSetting) {
            $recaptchaSetting = $recaptchaSettingsTable->newEntity(
                $recaptchaSettingsTable->getDefaultFieldValues(),
                ['validate' => false]
            );
        }

        if (!($recaptchaSetting instanceof RecaptchaSetting)) {
            throw new CakeException();
        }

        if ($this->getRequest()->is('post')) {
            $recaptchaSettingsTable->patchEntity($recaptchaSetting, (array)$this->getRequest()->getData());

            if (
                $recaptchaSettingsTable->save($recaptchaSetting, [
                    'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'recaptchaFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Recaptcha',
                    'action' => 'edit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'recaptchaErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set([
            'recaptchaSetting' => $recaptchaSetting,
            'valueOptions' => $recaptchaSettingsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * test method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function test()
    {
        /** @var \App\Model\Table\RecaptchaSettingsTable $recaptchaSettingsTable */
        $recaptchaSettingsTable = $this->getTableLocator()->get('RecaptchaSettings');

        $recaptchaSetting = $recaptchaSettingsTable->getData();
        if (!isset($recaptchaSetting)) {
            throw new NotFoundException();
        }
        $recaptchaSetting->set('use_flg', RecaptchaSetting::USE_FLG_ON);

        if ($this->getRequest()->is('post')) {
            try {
                if (!$recaptchaSetting->canTest()) {
                    throw new BadRequestException();
                }

                $this->Recaptcha->verify(RecaptchaSetting::ACTION_TEST, false);

                $this->Flash->set('動作確認に成功しました。', [
                    'key' => 'recaptchaTestFinish',
                    'element' => 'success',
                ]);
            } catch (BadRequestException $e) {
                $this->Flash->set('動作確認に失敗しました。設定をご確認ください。', [
                    'key' => 'recaptchaTestErrors',
                    'element' => 'error',
                ]);
            }
        }
    }
}
