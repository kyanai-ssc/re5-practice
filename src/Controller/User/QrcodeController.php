<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Utility\QrCodeUtility;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Qrcode Controller
 */
class QrcodeController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->viewBuilder()->setLayout(null);
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->Authentication->addUnauthenticatedActions([
            'view',
        ]);

        return $response;
    }

    /**
     * QRコード生成
     *
     * @param string|null $qrCode QRコード
     * @return \Cake\Http\Response|null|void|\Cake\Http\Exception\NotFoundException
     */
    public function view($qrCode = null)
    {
        if (!$qrCode) {
            throw new NotFoundException();
        }

        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->fetchTable('Reservations');

        $reservation = $reservationsTable->getByQrCode($qrCode, true);

        if (empty($reservation)) {
            throw new NotFoundException();
        }

        $response = $this->response;
        $response = $response->withStringBody(QrCodeUtility::createQrCodeImage($qrCode));

        $response = $response->withType('image/png');

        return $response;
    }
}
