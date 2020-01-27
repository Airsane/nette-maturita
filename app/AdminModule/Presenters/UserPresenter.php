<?php


namespace App\AdminModule\Presenters;

use App\Model\ReservationManager;
use App\Model\UserManager;
use Nette;

class UserPresenter extends BasePresenter
{
    private $database;
    private $userManager;
    private $reservationManager;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->userManager = new UserManager($this->database);
        $this->reservationManager = new ReservationManager($this->database);
    }

    public function handleDelete(int $userId)
    {
        //TODO: Odebrání uživatele
    }

    public function handleSetAdmin(int $userId)
    {
        $this->userManager->setAdmin($userId);
    }

    public function renderShow(int $id): void
    {
        $userD = $this->database->table('user')->get($id);
        if (!$userD) {
            $this->error("Uživatel nenalezeno!");
        }
        $this->template->userD = $userD;
        $this->template->reservations = $this->reservationManager->getUserReservations($id, 0, 10);
        $this->template->reservationCount = $this->reservationManager->getReservationCountUser($id);
    }

    public function renderDefault()
    {
        $this->template->userCount = $this->userManager->getUserCount();
        $this->template->users = $this->userManager->getUsers($this->getUser()->getId(), 0, 10);
    }

}