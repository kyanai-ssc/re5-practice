<?php
declare(strict_types=1);

namespace App\Auth;

use Cake\Auth\BasicAuthenticate as CakeBasicAuthenticate;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Http\ServerRequest;
use Cake\Utility\Hash;

class BasicAuthenticate extends CakeBasicAuthenticate
{
    /**
     * ベーシック認証
     *
     * @param \Cake\Http\ServerRequest $request A request object.
     * @param array $basic basicAuth User/PW
     * @return bool
     */
    public function unauthenticatedBasic(ServerRequest $request, array $basic)
    {
        $hasher = new DefaultPasswordHasher();
        $userName = Hash::get($request->getServerParams(), 'PHP_AUTH_USER', '');
        $password = Hash::get($request->getServerParams(), 'PHP_AUTH_PW', '');

        if ($hasher->check($password, Hash::get($basic, $userName, '$2y$10$abcdefghijklmnopqrstuv'))) {
            return true;
        }

        return false;
    }
}
