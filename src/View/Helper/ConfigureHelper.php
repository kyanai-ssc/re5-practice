<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\View\Helper;

/**
 * ConfigureHelper class.
 */
class ConfigureHelper extends Helper
{
    /**
     * Used to read information stored in Configure.
     *
     * @param string|null $var Variable to obtain. Use '.' to access array elements.
     * @param mixed $default The return value when the configure does not exist
     * @return mixed Value stored in configure, or null.
     */
    public function read(?string $var = null, $default = null)
    {
        return Configure::read($var, $default);
    }

    /**
     * Used to get information stored in Configure.
     *
     * @param string $var Variable to obtain. Use '.' to access array elements.
     * @return mixed Value stored in configure.
     */
    public function readOrFail(string $var)
    {
        return Configure::readOrFail($var);
    }

    /**
     * Returns true if given variable is set in Configure.
     *
     * @param string $var Variable name to check for
     * @return bool True if variable is there
     */
    public function check(string $var)
    {
        return Configure::check($var);
    }
}
