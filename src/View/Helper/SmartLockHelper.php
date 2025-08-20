<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Utility\SmartLock\SmartLockLinkage;
use Cake\View\Helper;

/**
 * SmartLockHelper class.
 */
class SmartLockHelper extends Helper
{
    /**
     * @var \App\Utility\SmartLock\SmartLockLinkage|null
     */
    protected $smartLock = null;

    /**
     * SmartLockLinkageインスタンスを取得
     *
     * @return \App\Utility\SmartLock\SmartLockLinkage
     */
    protected function getSmartLock()
    {
        if (!($this->smartLock instanceof SmartLockLinkage)) {
            $this->smartLock = new SmartLockLinkage();
        }

        return $this->smartLock;
    }

    /**
     * リモートロック利用か
     *
     * @return bool
     */
    public function useRemoteLock()
    {
        /** @var \App\Utility\SmartLock\SmartLockLinkage $smartLock */
        $smartLock = $this->getSmartLock();

        return $smartLock->useRemoteLock();
    }

    /**
     * アケルン利用か
     *
     * @return bool
     */
    public function useAkerun()
    {
        /** @var \App\Utility\SmartLock\SmartLockLinkage $smartLock */
        $smartLock = $this->getSmartLock();

        return $smartLock->useAkerun();
    }

    /**
     * スマートロックの名前を取得
     *
     * @return string
     */
    public function getName()
    {
        /** @var \App\Utility\SmartLock\SmartLockLinkage $smartLock */
        $smartLock = $this->getSmartLock();

        return $smartLock->getName();
    }

    /**
     * スマートロック設定．事務所IDを取得
     *
     * @return mixed|null
     */
    public function getOrganizationsId()
    {
        /** @var \App\Utility\SmartLock\SmartLockLinkage $smartLock */
        $smartLock = $this->getSmartLock();

        return $smartLock->getOrganizationsId();
    }
}
