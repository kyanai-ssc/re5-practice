<?php
declare(strict_types=1);

namespace App\Utility\Database;

/**
 * BuilderFactory Class.
 */
class BuilderFactory
{
    /**
     * @var array
     */
    protected static $instance = [];

    /**
     * ビルダーの単一インスタンスを取得
     *
     * @param string $name ドライバ名
     * @return \App\Utility\Database\AbstractBuilder ビルダー
     */
    public static function getInstance(string $name)
    {
        if (!isset(static::$instance[$name])) {
            $class = '\\App\\Utility\\Database\\Builder\\' . $name;
            static::$instance[$name] = new $class();
        }

        return static::$instance[$name];
    }
}
