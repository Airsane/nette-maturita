<?php


namespace App\Presenters;

class ReservationPresenter extends BasePresenter
{
    public function renderDefault()
    {
        $this->template->reservations = $this->reservationManager->getUserReservations($this->getUser()->getId(), 0, 20);
        $this->template->reservationCount = $this->reservationManager->getReservationCountUser($this->getUser()->getId());
    }
}