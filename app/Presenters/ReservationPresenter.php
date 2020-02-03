<?php


namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;

class ReservationPresenter extends BasePresenter
{

    /** @persistent */
    public $reservation;

    public function renderDefault()
    {
        $this->template->reservations = $this->reservationManager->getUserReservations($this->getUser()->getId(), 0, 20);
        $this->template->reservationCount = $this->reservationManager->getReservationCountUser($this->getUser()->getId());
    }

    public function renderShow($id)
    {
        $this->reservation = $this->reservationManager->getReservation($id);
        if (!$this->reservation) {
            $this->error("Rezervace nenalezena");
        }
        $this->template->reservation = $this->reservation;
        $this->template->status = $this->database->table('status');

    }

    public function bedsFormSucceded(Form $form, \stdClass $values)
    {
        $reservation = $this->reservationManager->getReservation($this->getParameter('id'));
        if (date('Y-m-d') >= date('Y-m-d', strtotime($reservation->start)) || $reservation->status_id == 3 || $reservation->status_id == 4) {
            $this->flashMessage('Chyba nemůžeš upravovat počet postelí po termínu', 'danger');
            $this->redirect('this');
        }
        $this->database->table('reservation')->get($this->getParameter('id'))->update(['beds' => $values->beds]);
        $this->flashMessage('Počet postelí změněn!', 'success');
        $this->redirect('this');
    }

    public function handleCancelReservation(int $id)
    {
        $this->database->table('reservation')->get($id)->update(['status_id' => 4]);
    }

    public function actionConfirm($resId, $code)
    {
        if (!isset($resId) || !isset($code)) {
            $this->flashMessage('Neplatný odkaz!', 'danger');
            $this->redirect('Homepage:default');
        }
        $reservation = $this->reservationManager->getReservation($resId);
        if ($reservation->status_id != 1) {
            $this->flashMessage('Už nemůžete potvrdit rezervaci!', 'danger');
            $this->redirect('Homepage:default');
        }
        if ($reservation->code == $code) {
            $html = '<div class="invoice-box"> <table cellpadding="0" cellspacing="0"> <tr class="top"> <td colspan="2"> <table> <tr> <td> Faktura #: ' . $reservation->id . '<br> </td> </tr> </table> </td> </tr> <tr class="information"> <td colspan="2"> <table> <tr> <td> Dodavatel:<br> <b>Kratos</b><br> Švermova 488<br> 460 01 Liberec 1 </td> <td> Odběratel: <br> ' . $reservation->firstname . '.' . $reservation->lastname . '<br> ' . $reservation->email . ' </td> </tr> </table> </td> </tr> <tr class="heading"> <td> Ubytování </td> <td> Cena </td> </tr> <tr class="item"> <td> ' . $reservation->name . ' (za ' . $reservation->DateDiff . ' noc/noci) </td> <td> ' . number_format($reservation->price * $reservation->DateDiff, 0, ',', ' ') . ' Kč </td> </tr> <tr class="total"> <td></td> <td> Celkově: ' . number_format($reservation->price * $reservation->DateDiff, 0, ',', ' ') . ' Kč</td> </tr> </table> </div>';
            $mpdf = new \Mpdf\Mpdf();
            $mpdf->WriteHTML(".invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, .15); font-size: 16px; line-height: 24px; font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555; } .invoice-box table { width: 100%; line-height: inherit; text-align: left; } .invoice-box table td { padding: 5px; vertical-align: top; } .invoice-box table tr td:nth-child(2) { text-align: right; } .invoice-box table tr.top table td { padding-bottom: 20px; } .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #333; } .invoice-box table tr.information table td { padding-bottom: 40px; } .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; } .invoice-box table tr.details td { padding-bottom: 20px; } .invoice-box table tr.item td{ border-bottom: 1px solid #eee; } .invoice-box table tr.item.last td { border-bottom: none; } .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; } @media only screen and (max-width: 600px) { .invoice-box table tr.top table td { width: 100%; display: block; text-align: center; } .invoice-box table tr.information table td { width: 100%; display: block; text-align: center; } } /** RTL **/ .rtl { direction: rtl; font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; } .rtl table { text-align: right; } .rtl table tr td:nth-child(2) { text-align: left; }", 1);
            $mpdf->WriteHTML($html);
            $mpdf->Output('./invoice/' . $this->getUser()->getId() . '-' . $reservation->house_id . '.pdf');
            $this->database->table('reservation')->get($resId)->update(['status_id' => 2]);
            $this->flashMessage('Rezervace potvrzena! Faktura odeslána na email', 'success');
            $mail = new Message;
            $email = $this->database->table('user')->get($this->getUser()->getId())->email;
            $mail->setFrom('Kratos <info@kabelepa.spse-net.cz>')
                ->addTo($email)
                ->setSubject('Potvrzení rezervace')
                ->setBody("Dobrý den,\nvaše rezervace byla potvrzena.")
                ->addAttachment('./invoice/' . $this->getUser()->getId() . '-' . $reservation->house_id . '.pdf');
            $mailer = new SendmailMailer;
            $mailer->send($mail);
            $this->redirect('Homepage:default');
        }
        $this->flashMessage('Špatný kód!', 'danger');
        $this->redirect('Homepage:default');
    }

    protected function createComponentBedsForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->getElementPrototype()->setAttribute('data-ajax', 'true');
        $form->setRenderer(new BootstrapVerticalRenderer);
        $reservation = $this->reservationManager->getReservation($this->getParameter('id'));
        $form->addText('beds', "Počet postelí:")->setType("number")->setRequired('Prosím zadejte počet postelí')->addRule(Form::INTEGER, 'Počet postelí musí být číslo')->addRule(Form::RANGE, 'Pocet postelí musí být od %d do %d', [1, $this->database->table('house')->get($reservation->house_id)->beds]);
        $form->addSubmit('send', 'Změnit');
        $form->setDefaults(['beds' => $reservation->beds]);
        $form->onSuccess[] = [$this, 'bedsFormSucceded'];
        return $form;
    }


}