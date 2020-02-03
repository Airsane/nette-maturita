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

    public function handleDeleteUser(int $userId)
    {
        $this->database->table('reservation')->where('user_id = ?', $userId)->delete();
        $this->database->table('user')->get($userId)->delete();
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
        $this->template->avgPrice = $this->reservationManager->getUserAveragePrice($id);
        $this->template->avgBeds = $this->reservationManager->getUserAverageBeds($id);
    }

    public function renderDefault($page = 1)
    {
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemsPerPage(20);
        $paginator->setPage($page);
        $userCount = $this->userManager->getUserCount();
        $paginator->setItemCount($userCount);
        $this->template->userCount = $userCount;
        $this->template->users = $this->userManager->getUsers($this->getUser()->getId(), $paginator->getOffset(), $paginator->getLength());
        $this->template->paginator = $paginator;
    }

}