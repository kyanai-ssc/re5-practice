<?php
declare(strict_types=1);

namespace App\Command\Seed;

use App\Command\Traits\SeedTrait;
use Migrations\AbstractSeed as BaseAbstractSeed;

/**
 * AbstractSeed class.
 */
class AbstractSeed extends BaseAbstractSeed
{
    use SeedTrait;
}
