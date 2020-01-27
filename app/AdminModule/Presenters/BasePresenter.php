<?php


namespace App\AdminModule\Presenters;

use Nette;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    public function beforeRender()
    {
        if (!$this->getUser()->isLoggedIn() || $this->getUser()->getRoles()[0] != 1) {
            $this->flashMessage('Nemáš sem přístup!');
            $this->redirect(':Homepage:default');
        }
    }

}