<?php
/**
 * Created by PhpStorm.
 * User: patri
 * Date: 27.03.2019
 * Time: 8:23
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;


class SignPresenter extends BasePresenter
{

    public function actionOut()
    {
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení bylo úspěšné!', 'success');
        $this->redirect('Homepage:');
    }

    public function actionConfirm($user, $code)
    {
        if (!isset($user) || !isset($code)) {
            $this->flashMessage('Neplatný odkaz!', 'danger');
            $this->redirect('Homepage:default');
        }

        $userD = $this->database->table('user')->get($user);
        if ($userD) {
            if ($userD->active == 1) {
                $this->flashMessage('Účet je již aktivován!', 'danger');
                $this->redirect('Homepage:default');
            }
            if ($userD->code == $code) {
                $userD->update(['active' => 1]);
                $this->flashMessage('Email ověřen, učet je aktivován!', 'success');
                $this->redirect('Homepage:default');
            } else {
                $this->flashMessage('Neplatný kód!', 'danger');
                $this->redirect('Homepage:default');
            }
        } else {
            $this->flashMessage('Uživatel nenalezen!', 'danger');
            $this->redirect('Homepage:default');
        }
    }

    public function registerFormSucceeded(Form $form, \stdClass $values)
    {
        if ($this->user->isLoggedIn()) {
            $this->flashMessage("Chyba nepatříš sem!", 'danger');
            $this->redirect('Homepage:default');
            exit;
        }
        try {
            $this->database->beginTransaction();
            try {
                $code = substr(md5(uniqid(strval(mt_rand()))), 0, 8);
                $row = $this->database->table('user')->insert(array('firstname' => $values->firstname, 'lastname' => $values->lastname, 'password' => (new \Nette\Security\Passwords)->hash($values->password), 'email' => $values->email, 'phone' => $values->phone, 'code' => $code));
                $this->database->commit();
                $mail = new Message;
                $mail->setFrom('Kratos <info@kabelepa.spse-net.cz>')
                    ->addTo($values->email)
                    ->setSubject('Ověření emailu')
                    ->setBody("Dobrý den,\nprosím ověřte svůj email tímto odkazem https://kabelepa.mp.spse-net.cz/www/sign/confirm?user=" . $row->id . "&code=" . $code . "");
                $mailer = new SendmailMailer;
                $mailer->send($mail);
                $this->flashMessage("Účet byl vytvořen! Ověřovací email byl odeslán!", "success");
                $this->redirect('Homepage:');
            } catch (PDOException $e) {
                $this->database->rollBack();
                $this->flashMessage('Registrace se nepodařila!', 'danger');
            }
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            $this->flashMessage('Registrace se nepodařila. Email je již zabraný!', 'danger');
        }
    }

    protected function createComponentRegisterForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addEmail('email', 'Email:')->setRequired('Prosím vyplňte email');
        $form->addText('phone', 'Telefon:')->setRequired('Prosím vyplňte telefon')->addRule(Form::PATTERN, 'Špatný telefon', '[0-9]{9}');
        $form->addText('firstname', 'Jméno:')->setRequired('Prosím vyplňte jméno');
        $form->addText('lastname', 'Příjmení:')->setRequired('Prosím vyplňte příjmení');
        $form->addPassword('password', 'Heslo:')->setRequired('Prosím zadejte heslo')->addRule(Form::MIN_LENGTH, 'Minimální délka hesla je %d znaků', 5);
        $form->addPassword('passwordVerify', 'Heslo znovu:')->setRequired('Prosím zadejte heslo')->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password']);
        $form->addSubmit('send', 'Registrovat se');
        $form->onSuccess[] = [$this, 'registerFormSucceeded'];

        return $form;
    }
}
