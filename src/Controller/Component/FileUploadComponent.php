<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Utility\ArrayUtility;
use Cake\Controller\Component;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;

/**
 * ファイルアップロードコンポーネント
 */
class FileUploadComponent extends Component
{
    /**
     * @var string
     */
    protected $uploadToken = 'file_uploads_token';

    /**
     * @var \Cake\Http\Session|null
     */
    protected $storage = null;

    /**
     * @var mixed|null
     */
    protected $storageKeyPrefix = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->storage = $this->getController()->getRequest()->getSession();
        $this->storageKeyPrefix = Hash::get($config, 'storageKey', 'inputFile');
    }

    /**
     * データを取得する
     *
     * @param string $key キー
     * @param mixed $default デフォルト値
     * @return mixed データ
     */
    public function get(string $key, $default)
    {
        if (!$this->getStorage()->check($this->storageKey($key))) {
            return $default;
        }

        return $this->getStorage()->read($this->storageKey($key));
    }

    /**
     * データを取得する
     *
     * @return mixed データ
     */
    public function getTokenName()
    {
        return $this->uploadToken;
    }

    /**
     * データを設定する
     *
     * @param string $name キー
     * @return mixed データ
     */
    public function setTokenName(string $name)
    {
        $this->uploadToken = $name;
    }

    /**
     * データを設定する
     *
     * @param string $key キー
     * @param mixed $data データ
     * @return void
     */
    public function set(string $key, $data)
    {
        $this->getStorage()->write($this->storageKey($key), $data);
    }

    /**
     * データを削除する
     *
     * @param string $key キー
     * @return void
     */
    public function delete(string $key)
    {
        $this->getStorage()->delete($this->storageKey($key));
    }

    /**
     * ストレージを取得
     *
     * @return \Cake\Http\Session
     */
    protected function getStorage()
    {
        if (!isset($this->storage)) {
            throw new CakeException();
        }

        return $this->storage;
    }

    /**
     * ストレージの格納キーを生成する
     *
     * @param string $key キー
     * @return string 格納キー
     */
    protected function storageKey($key)
    {
        if (!isset($this->storageKeyPrefix) || $this->storageKeyPrefix === '') {
            return $key;
        }

        return $this->storageKeyPrefix . '.' . $key;
    }

    /**
     * セッション情報に配列を追加
     *
     * @param string $key セッションキー
     * @param array $add 追加データ
     * @return void
     */
    public function add(string $key, array $add)
    {
        $data = $this->get($key, []);
        $data[] = $add;
        $this->set($key, $data);
    }

    /**
     * セッション情報に配列を更新
     *
     * @param string $key セッションキー
     * @param array $update 更新データ
     * @param string $modifyIndex 更新するキー値
     * @return void
     */
    public function modify(string $key, array $update, $modifyIndex)
    {
        $data = $this->get($key, []);
        $data[$modifyIndex] = $update;
        $this->set($key, $data);
    }

    /**
     * 配列のキーの最大値＋１を返却
     *
     * @param string $key キー
     * @return int
     */
    public function nextIndex(string $key)
    {
        $list = $this->get($key, null);

        if (is_array($list)) {
            $next = (int)ArrayUtility::arrayMax(array_keys($list));
        } else {
            $next = 0;
        }

        return $next;
    }

    /**
     * ファイル管理用デフォルトバリューの設定
     *
     * @param array $files ファイル
     * @param string $key キー
     * @return array
     */
    public function buildDefaultValue(array $files, string $key)
    {
        $data = [];
        foreach ($files as $file) {
            $data[] = [
                'original_file_name' => $file['file_name'],
                'file' => null,
                'ext' => pathinfo($file['file_name'], PATHINFO_EXTENSION),
                'size' => $file['size'],
                'new' => false,
            ];
        }

        $this->set($key, $data);

        return $data;
    }
}
