<?php


namespace App\Presenters;

use App\Model\MyAuthenticator;
use Nette;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;


abstract class BasePresenter extends Nette\Application\UI\Presenter
{

    public $database;
    public $autentificator;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->autentificator = new MyAuthenticator($database);
    }

    public function loginFormSucceeded(Form $form, \stdClass $values)
    {
        if ($this->user->isLoggedIn()) {
            $this->flashMessage("Error you dont belong here");
            $this->redirect('Homepage:default');
            exit;
        }
        try {
            $this->getUser()->setAuthenticator($this->autentificator);
            $this->getUser()->login($values->email, $values->password);
            $this->flashMessage('Přihlášení bylo úspešné.', '-success');
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $this->flashMessage('Neplatné jméno nebo heslo!');

        }
    }

    protected function createComponentLoginForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addEmail('email', 'Email:')->setRequired('Prosím vyplňte email');
        $form->addPassword('password', 'Heslo:')->setRequired('Prosím zadejte heslo');
        $form->addSubmit('send', 'Přihlásit se');
        $form->onSuccess[] = [$this, 'loginFormSucceeded'];
        return $form;
    }
}