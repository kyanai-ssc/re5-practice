<?php
declare(strict_types=1);

namespace App\Model\InputType;

use Cake\Core\Configure;

/**
 * InputTypeManagerFactory class.
 */
class InputTypeManagerFactory
{
    /**
     * @var array
     */
    protected static $instance = [];

    /**
     * 入力タイプマネージャの単一インスタンスを取得
     *
     * @param int $inputType 入力タイプ
     * @param bool $isAdmin 管理者側フラグ
     * @return \App\Model\InputType\AbstractInputTypeManager 入力タイプマネージャ
     */
    public static function getInstance(int $inputType, bool $isAdmin)
    {
        if (!isset(static::$instance[$inputType])) {
            $class = '\\App\\Model\\InputType\\Manager\\' . Configure::readOrFail(
                'Master.form.inputTypeClass.' . $inputType
            );
            static::$instance[$inputType] = new $class($inputType, $isAdmin);
        }

        return static::$instance[$inputType];
    }
}
