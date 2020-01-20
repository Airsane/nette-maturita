<?php


namespace App\AdminModule\Presenters;

use App\Model\UserManager;
use Nette;

class UserPresenter extends Nette\Application\UI\Presenter
{
    private $database;
    private $userManager;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->userManager = new UserManager($this->database);
    }

    public function renderShow(int $id): void
    {
        $userD = $this->database->table('user')->get($id);
        if (!$userD) {
            $this->error("Uživatel nenalezeno!");
        }
        $this->template->userD = $userD;
    }

    public function renderDefault()
    {
        $this->template->userCount = $this->userManager->getUserCount();
        $this->template->users = $this->userManager->getUsers(0, 10);
    }

}