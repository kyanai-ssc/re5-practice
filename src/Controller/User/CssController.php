<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Locale\Message;
use Cake\Http\Exception\NotFoundException;
use SplFileInfo;

/**
 * Css controller
 *
 * DBの参照やセッションの取得など一切行わずファイルのあるなしで判断する
 */
class CssController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->loadComponent('FileDownload');
        $this->loadComponent('Frame');

        if (!is_null(env(static::ENV_X_FRAME_OPTIONS_UNSET))) {
            $this->Frame->sameorigin();
        }

        $this->canAccess('user');
    }

    /**
     * Index method CSS出力
     *
     * @param string $fileName ファイル名
     * @return \Cake\Http\Response|null|void
     */
    public function index($fileName = null)
    {
        $response = $this->getResponse();
        $response->getBody()->close();

        $response = $response->withCharset('UTF-8')
            ->withType('css');

        if (is_null($fileName)) {
            throw new NotFoundException(Message::ERROR_NOT_FOUND);
        }

        $response = $response->withStringBody('');
        $file = new SplFileInfo(CUSTOM_CSS);
        if ($file->isReadable()) {
            $css = file_get_contents($file->getPathname());
            if ($css !== false) {
                $response = $response->withStringBody($css);
            }
        }
        $this->setResponse($response);

        return $this->getResponse();
    }
}
