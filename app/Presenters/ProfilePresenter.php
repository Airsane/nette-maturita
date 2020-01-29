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

    public function renderTest()
    {
        //TODO: dodělat fakturu
        $reservation = $this->reservationManager->getReservation(9);
        $this->template->reservation = $reservation;
        $html = '<div class="container"> <div class="card"> <div class="card-header"> Faktura <strong>' . date('d.m.Y') . '</strong> <span class="float-right"> <strong>Status:</strong> ' . $reservation->value . '</span></div> <div class="card-body"> <div class="row mb-4"> <div class="col-sm-6"><h6 class="mb-3">From:</h6> <div><strong>Kratos</strong></div> <div>Švermova 488</div> <div>460 01 Liberec 1</div> </div> <div class="col-sm-6"><h6 class="mb-3">To:</h6> <div><strong>' . $reservation->firstname . ' ' . $reservation->lastname . '</strong></div> <div>Email: ' . $reservation->email . '</div> </div> </div> <div class="table-responsive-sm"> <table class="table table-striped"> <thead> <tr> <th>Ubytování<th> <th class="right">Cena za noc</th> <th class="center">Počet nocí</th> <th class="right">Celkem</th> </tr> </thead> <tbody> <tr> <td class="left strong">' . $reservation->name . '</td> <td></td> <td class="right">' . number_format($reservation->price, 0, ',', ' ') . ' Kč</td> <td class="center">' . $reservation->DateDiff . '</td> <td class="right">' . number_format($reservation->price * $reservation->DateDiff, 0, ',', ' ') . ' Kč</td> </tr> </tbody> </table> </div> <div class="row"> <div class="col-lg-4 col-sm-5"></div> <div class="col-lg-4 col-sm-5 ml-auto"> <table class="table table-clear"> <tbody> <tr> <td class="left"><strong>Celkem</strong></td> <td class="right"><strong>' . number_format($reservation->price * $reservation->DateDiff, 0, ',', ' ') . ' Kč</strong></td> </tr> </tbody> </table> </div> </div> </div> </div> </div>';
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML(file_get_contents('res/css/bootstrap3.min.css'), 1);
        $mpdf->WriteHTML($html);
        $mpdf->Output('test.pdf');
    }

    public function changePasswordSucceded(Form $form, \stdClass $values)
    {
        if ($this->userManager->changePassword($values->oldPass, $values->password, $this->getUser()->getId())) {
            $this->flashMessage('Heslo změněno!', 'success');
            $this->redirect('this');
        } else {
            $this->flashMessage('Špatné heslo!', 'danger');
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