<?php
declare(strict_types=1);

namespace App\Utility\CommonData;

use App\Mailer\Mailer;
use App\Model\Entity\Admin;
use App\Model\Entity\User;
use App\Model\Entity\UserAuthority;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenTime;
use Cake\Utility\Hash;

/**
 * CommonData Class.
 */
class CommonData
{
    protected ?FrozenTime $nowDateTime;
    protected ?Admin $adminLoginData;
    protected ?User $userLoginData;

    protected ?int $userLabelId;
    protected ?UserAuthority $userAuthority;

    /**
     * 現在日時を取得
     *
     * @return \Cake\I18n\FrozenTime 現在日時
     */
    public function getNowDateTime(): FrozenTime
    {
        if (!isset($this->nowDateTime)) {
            $this->nowDateTime = FrozenTime::now();
        }

        return $this->nowDateTime;
    }

    /**
     * 現在日時を設定
     *
     * @param \Cake\I18n\FrozenTime $nowDateTime 現在日時
     * @return void
     */
    public function setNowDateTime(FrozenTime $nowDateTime): void
    {
        $this->nowDateTime = $nowDateTime;
    }

    /**
     * 管理者側ログイン情報の有無を判定
     *
     * @return bool 判定結果
     */
    public function existsAdminLoginData(): bool
    {
        return isset($this->adminLoginData);
    }

    /**
     * 管理者側ログイン情報を取得
     *
     * @return \App\Model\Entity\Admin ログイン情報
     */
    public function getAdminLoginData(): Admin
    {
        if (!isset($this->adminLoginData)) {
            throw new CakeException();
        }

        return $this->adminLoginData;
    }

    /**
     * 管理者側ログイン情報を設定
     *
     * @param \App\Model\Entity\Admin $admin ログイン情報
     * @return void
     */
    public function setAdminLoginData(Admin $admin): void
    {
        $this->adminLoginData = $admin;
    }

    /**
     * 管理者側ログイン情報を削除
     *
     * @return void
     */
    public function removeAdminLoginData(): void
    {
        if (isset($this->adminLoginData)) {
            unset($this->adminLoginData);
        }
    }

    /**
     * 利用者側ログイン情報の有無を判定
     *
     * @return bool 判定結果
     */
    public function existsUserLoginData(): bool
    {
        return isset($this->userLoginData);
    }

    /**
     * 利用者側ログイン情報を取得
     *
     * @return \App\Model\Entity\User ログイン情報
     */
    public function getUserLoginData(): User
    {
        if (!isset($this->userLoginData)) {
            throw new CakeException();
        }

        return $this->userLoginData;
    }

    /**
     * 利用者側ログイン情報を設定
     *
     * @param \App\Model\Entity\User $user ログイン情報
     * @return void
     */
    public function setUserLoginData(User $user): void
    {
        $this->userLoginData = $user;
    }

    /**
     * 利用者側ログイン情報を削除
     *
     * @return void
     */
    public function removeUserLoginData(): void
    {
        if (isset($this->userLoginData)) {
            unset($this->userLoginData);
        }
    }

    /**
     * 管理者側ログインのラベルを返却
     *
     * @return int|null ラベルID
     */
    public function getAdminLoginLabel(): ?int
    {
        return $this->getAdminLoginData()->get('label_id');
    }

    /**
     * 会員権限取得
     *
     * @return array
     */
    public function getAccessUserData(): array
    {
        $userData = [
            'labelId' => $this->getUserLabelId(),
            'userAuthorityId' => $this->getUserAuthority()->get('id'),
        ];

        return $userData;
    }

    /**
     * 会員権限取得
     *
     * @return \App\Model\Entity\UserAuthority 会員権限
     */
    public function getUserAuthority(): UserAuthority
    {
        if (!isset($this->userAuthority)) {
            throw new CakeException();
        }

        return $this->userAuthority;
    }

    /**
     * 会員権限設定
     *
     * @param \App\Model\Entity\UserAuthority $userAuthority 権限
     * @return void
     */
    public function setUserAuthority(UserAuthority $userAuthority): void
    {
        $this->userAuthority = $userAuthority;
    }

    /**
     * 公開側ラベル情報の取得
     *
     * @return int|null
     */
    public function getUserLabelId(): ?int
    {
        if (!isset($this->userLabelId)) {
            return null;
        }

        return $this->userLabelId;
    }

    /**
     * 公開側ラベル情報を設定
     *
     * @param int|null $labelId ラベルID
     * @return void
     */
    public function setUserLabelId(?int $labelId): void
    {
        $this->userLabelId = $labelId;
    }

    /**
     * メール送信時のデフォルトFromアドレスを取得
     *
     * @param bool $domainOnly ドメイン（@付き）のみ返すかどうか
     * @return string
     */
    public function getDefaultFromAddress(bool $domainOnly = false): string
    {
        $address = Mailer::getDefaultFromAddress();

        if ($domainOnly) {
            $parts = (array)explode('@', $address);

            return '@' . Hash::get($parts, '1', '');
        } else {
            return $address;
        }
    }

    /**
     * メール送信時のデフォルトFromアドレスが環境別設定ファイルに設定されているか判定
     *
     * @return bool
     */
    public function existsDefaultFromAddressOnEnv(): bool
    {
        return Mailer::existsDefaultFromAddressOnEnv();
    }
}
