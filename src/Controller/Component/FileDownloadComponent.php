<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Http\Stream;
use App\Utility\CommonData\CommonDataTrait;
use Cake\Controller\Component;
use Cake\Core\Exception\CakeException;
use Cake\Http\CallbackStream;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * ファイルダウンロードコンポーネント
 */
class FileDownloadComponent extends Component
{
    use CommonDataTrait;

    /**
     * ダウンロードのレスポンスを設定
     *
     * @param string $name ファイル名
     * @param string|null $type ファイル種別
     * @param string $path ファイルパス
     * @param bool $cleanup ファイル削除
     * @param bool $download ダウンロード
     * @return \Cake\Http\Response レスポンス
     */
    public function setDownloadResponse(
        string $name,
        ?string $type,
        string $path,
        bool $cleanup = false,
        bool $download = true
    ): Response {
        $response = $this->getController()->getResponse();
        try {
            $response = $response->withFile($path, [
                'download' => $download,
            ]);
        } catch (NotFoundException $e) {
            throw new CakeException($e->getMessage(), null, $e);
        }

        if ((string)$type !== '') {
            $response = $response->withType((string)$type);
        }
        if ($download) {
            $response = $this->setDownloadHeader($response, $name);
        }

        if ($cleanup) {
            $response->getBody()->close();

            $stream = new Stream($path, 'rb');
            $stream->setCleanup($cleanup);
            $response = $response->withBody($stream);
        }

        $this->getController()->setResponse($response);

        return $this->getController()->getResponse();
    }

    /**
     * ストリームダウンロードのレスポンスを設定
     *
     * @param string $name ファイル名
     * @param string $type ファイル種別
     * @param callable $callback 出力処理
     * @return \Cake\Http\Response レスポンス
     */
    public function setStreamDownloadResponse(string $name, string $type, callable $callback): Response
    {
        $response = $this->getController()->getResponse();
        $response = $response->withType($type);
        $response = $this->setDownloadHeader($response, $name);

        $stream = new CallbackStream($callback);
        $response = $response->withBody($stream);

        $this->getController()->setResponse($response);

        return $this->getController()->getResponse();
    }

    /**
     * ダウンロード時のヘッダーを指定
     *
     * @param \Cake\Http\Response $response ファイル名
     * @param string $name ファイル名
     * @return \Cake\Http\Response レスポンス
     */
    public function setDownloadHeader(Response $response, string $name): Response
    {
        $response = $response->withHeader(
            'Content-Disposition',
            "attachment; filename*=utf-8''" . rawurlencode($name)
        );

        return $response;
    }

    /**
     * ファイルダウンロード時のファイル名を整形
     *
     * @param string $fileName ファイル名
     * @return string ファイル名
     */
    public function getDlFileName(string $fileName): string
    {
        $fileName = preg_replace('/%NOW%/', $this->commonData()->getNowDateTime()->format('YmdHis'), $fileName);
        if (!isset($fileName)) {
            throw new CakeException();
        }

        return $fileName;
    }
}
