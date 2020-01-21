<?php


namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;

class ProfilePresenter extends BasePresenter
{
    public function renderSettings()
    {

    }

    public function changePasswordSucceded(Form $form, \stdClass $values)
    {
        if ($this->userManager->changePassword($values->oldPass, $values->password, $this->getUser()->getId())) {
            $this->flashMessage('Heslo změněno!');
            $this->redirect('this');
        } else {
            $this->flashMessage('Špatné heslo!');
            $this->redirect('this');
        }
    }

    protected function createComponentChangePassword()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addPassword('oldPass', 'Staré heslo:')->setRequired('Prosím zadejte staré heslo');
        $form->addPassword('password', 'Nové heslo:')->setRequired('Prosím zadejte heslo')->addRule(Form::MIN_LENGTH, 'Minimální délka hesla je %d znaků', 5);
        $form->addPassword('passwordVerify', 'Heslo znovu:')->setRequired('Prosím zadejte heslo')->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);
        $form->addSubmit('send', 'Změnit heslo');
        $form->onSuccess[] = [$this, 'changePasswordSucceded'];

        return $form;
    }
}