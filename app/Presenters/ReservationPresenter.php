<?php


namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
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