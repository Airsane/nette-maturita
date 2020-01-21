<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model\ReservationManager;
use Nette;


final class ReservationPresenter extends Nette\Application\UI\Presenter
{
    private $database;
    private $reservationManager;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->reservationManager = new ReservationManager($database);
    }

    public function renderShow($id)
    {

    }

    public function renderDefault()
    {
        $this->template->reservationCount = $this->reservationManager->getReservationCount();
        $this->template->reservations = $this->reservationManager->getReservations(0, 10);
    }


}
