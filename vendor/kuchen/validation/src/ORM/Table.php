<?php

declare(strict_types=1);

namespace Kuchen\Validation\ORM;

use Cake\ORM\Table as CakeTable;
use Kuchen\Validation\Validation\Validator;

/**
 * Class Table
 *
 * @package App\ORM
 */
class Table extends CakeTable
{
    /**
     * Validator class.
     *
     * @var string
     */
    protected $_validatorClass = Validator::class; // phpcs:ignore
}
