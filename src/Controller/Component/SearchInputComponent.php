<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Validation\CustomValidation;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * 検索入力コンポーネント
 */
class SearchInputComponent extends Component
{
    /**
     * 検索実行クエリ
     *
     * @var array|null
     */
    protected $searchQuery = null;

    /**
     * 検索と同画面での保存実行
     *
     * @var array|null
     */
    protected $saveExec = null;

    /**
     * ページネーションのキー
     *
     * @var array
     */
    protected $paginatorKey = ['limit', 'sort', 'page', 'direction'];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->searchQuery = Hash::get($config, 'searchQuery', []);
        $this->saveExec = Hash::get($config, 'saveExec', []);
    }

    /**
     * 検索条件の維持を判定する
     *
     * @return bool 判定結果
     */
    public function shouldKeepCondition()
    {
        if (isset($this->searchQuery)) {
            foreach ($this->searchQuery as $queryKey => $queryValue) {
                if ($this->getController()->getRequest()->getQuery($queryKey) !== $queryValue) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 保存処理を実施するかを判定する
     *
     * @return bool 判定結果
     */
    public function shouldSaveExec()
    {
        if (isset($this->saveExec)) {
            foreach ($this->saveExec as $saveKey => $saveValue) {
                if ($this->getController()->getRequest()->getQuery($saveKey) !== $saveValue) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 検索条件を取得
     *
     * @param array $default デフォルト値
     * @param array|string|null $session セッションデータ
     * @param array|string|null $mergeData マージするデータ
     * @return array 検索条件
     */
    public function getCondition(array $default, $session = null, $mergeData = null)
    {
        $condition = [];

        if ($this->getController()->getRequest()->is('post')) {
            $condition += (array)$this->getController()->getRequest()->getData();
            $condition += (array)$this->getController()->getRequest()->getQuery();
            if (isset($mergeData) && is_array($mergeData)) {
                $condition += $mergeData;
            }
            if (isset($session) && is_array($session)) {
                $sessionPaginatorQuery = $this->getPaginatorQuery($session);
                unset($sessionPaginatorQuery['page']);
                $condition += $this->getPaginatorQuery($sessionPaginatorQuery);
            }
        } else {
            $condition += (array)$this->getController()->getRequest()->getQuery();
            if (isset($mergeData) && is_array($mergeData)) {
                $condition += $mergeData;
            }
            if (isset($session) && is_array($session)) {
                $condition += $session;
            } else {
                $condition += $default;
            }
        }
        $condition += $this->getPaginatorQuery($default);

        return $condition;
    }

    /**
     * 入力エラー時の検索条件を取得
     *
     * @param array $default デフォルト値
     * @param array|string|null $session セッションデータ
     * @param array|string|null $mergeData マージするデータ
     * @return array 検索条件
     */
    public function getFallbackCondition(array $default, $session = null, $mergeData = null)
    {
        $condition = $default;

        if (isset($session)) {
            if (is_string($session)) {
                $condition = [];
            } else {
                $condition = $session;
            }
        }
        if (isset($mergeData) && is_array($mergeData)) {
            $condition += $mergeData;
        }

        return $condition;
    }

    /**
     * ページネーション用に上書きするクエリを取得
     *
     * @param array $condition 検索条件
     * @return array クエリ
     */
    public function getPaginatorQuery(array $condition)
    {
        return array_intersect_key($condition, array_fill_keys($this->paginatorKey, true));
    }

    /**
     * 検索ボタンを押されたかどうかの判定
     *
     * @return bool
     */
    public function checkSearchButtonClick()
    {
        return $this->getController()->getRequest()->is('post');
    }

    /**
     * チェックされたIDを取得
     *
     * @param array|string|null $session セッションデータ
     * @return array ID一覧
     */
    public function getCheckedIds($session = null)
    {
        $allCheck = null;
        if (is_scalar($this->getController()->getRequest()->getData('allCheck'))) {
            $allCheck = $this->getController()->getRequest()->getData('allCheck');
        }
        if (((string)$allCheck) === (string)Configure::readOrFail('Master.common.listCheckId.check')) {
            return ['allCheck' => true];
        }

        $checked = [];
        if (isset($session) && is_array($session)) {
            $checked = Hash::remove($session, 'allCheck');
        }

        $ids = [];
        if (is_array($this->getController()->getRequest()->getData('ids'))) {
            $ids = $this->getController()->getRequest()->getData('ids');
        }
        foreach ($ids as $id) {
            if (CustomValidation::integer($id, CustomValidation::BIGINT_MAX)) {
                $checked[$id] = $id;
            }
        }

        $notCheckIds = [];
        if (is_array($this->getController()->getRequest()->getData('notCheckIds'))) {
            $notCheckIds = $this->getController()->getRequest()->getData('notCheckIds');
        }
        foreach ($notCheckIds as $id) {
            if (CustomValidation::integer($id, CustomValidation::BIGINT_MAX)) {
                unset($checked[$id]);
            }
        }

        return array_unique($checked);
    }

    /**
     * チェック情報を返却
     *
     * @param array $valueOptions valueOptions
     * @param array|string|null $checkList チェック情報
     * @return array $checkInfo
     */
    public function getListCheckInfo(array $valueOptions, $checkList)
    {
        if (is_null($checkList) || is_string($checkList)) {
            $checkList = [];
        }

        $checkInfo = [];

        $allChecked = false;
        $targetLabel = $valueOptions['listCheck'][$valueOptions['listCheckId']['check']];
        $allCheckedClass = '';
        if (isset($checkList['allCheck']) && $checkList['allCheck']) {
            $allChecked = true;
            $allCheckedClass = 'close';
            $targetLabel = $valueOptions['listCheck'][$valueOptions['listCheckId']['remove']];
        }

        $checkInfo['allCheckClass'] = $allCheckedClass;
        $checkInfo['allChecked'] = $allChecked;
        $checkInfo['targetLabel'] = $targetLabel;
        $checkInfo['checkList'] = $checkList;
        $checkInfo['allCheckLabelClass'] = '';

        if ($allChecked) {
            $checkInfo['allCheckLabelClass'] = 'close';
        }

        return $checkInfo;
    }
}
