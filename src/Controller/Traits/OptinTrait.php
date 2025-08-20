<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use App\Locale\Message;
use App\Model\Entity\OptinToken;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * Optin trait.
 */
trait OptinTrait
{
    /**
     * Mail action
     *
     * @param int $type タイプ
     * @param array|null $urlParameter URLパラメータ
     * @return bool
     */
    protected function mailAddAction($type, $urlParameter = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');
        /** @var \App\Model\Table\OptinTokensTable $optinTokensTable */
        $optinTokensTable = $this->fetchTable('OptinTokens');

        if (!$siteSettingsTable->getData()->isUseFlgOn('optin_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        if ($this->commonData()->existsUserLoginData()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if ($this->getRequest()->is('post')) {
            $optinToken = $optinTokensTable->newEntity($this->getRequest()->getData());
            $optinToken->set('type', $type);
            if ($optinTokensTable->save($optinToken, ['optinUrlParameter' => $urlParameter])) {
                return true;
            } else {
                if ($type === OptinToken::TYPE_RESERVATION) {
                    $this->Flash->set((string)__(Message::INVALID_INPUT), [
                        'key' => 'reservationsMailAddErrors',
                        'element' => 'error',
                    ]);
                } elseif ($type === OptinToken::TYPE_USER) {
                    $this->Flash->set((string)__(Message::INVALID_INPUT), [
                        'key' => 'userMailAddErrors',
                        'element' => 'error',
                    ]);
                }
            }
        } else {
            $optinToken = $optinTokensTable->newEntity([], ['validate' => false]);
        }

        $this->set([
            'optinToken' => $optinToken,
        ]);

        return false;
    }

    /**
     * Mail finish action
     *
     * @return void
     */
    protected function mailAddFinishAction()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        if (!$siteSettingsTable->getData()->isUseFlgOn('optin_flg')) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }
        if ($this->commonData()->existsUserLoginData()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }
    }

    /**
     * token action
     *
     * @param \App\Form\User\Optin\OptinForm $optinForm オプトインフォーム
     * @return void
     */
    protected function tokenAction($optinForm)
    {
        if ($this->commonData()->existsUserLoginData()) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        if (!$optinForm->execute((array)$this->getRequest()->getQuery())) {
            throw new BadRequestException(Message::ERROR_INVALID_URL);
        }
    }
}
