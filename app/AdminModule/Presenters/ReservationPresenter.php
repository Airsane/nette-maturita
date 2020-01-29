<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model\ReservationManager;
use Nette;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;


final class ReservationPresenter extends BasePresenter
{
    /** @persistent */
    public $reservation;
    private $database;
    private $reservationManager;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->reservationManager = new ReservationManager($database);
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

    public function statusFormSucceded(Form $form, \stdClass $values)
    {
        $this->database->table('reservation')->get($this->getParameter('id'))->update(['status_id' => $values->status]);
        $this->flashMessage('Status změněn!', 'success');
    }

    public function dateFormSucceded(Form $form, \stdClass $values)
    {
        $this->database->table('reservation')->get($this->getParameter('id'))->update(['start' => $values->from, 'end' => $values->to]);
        $this->flashMessage('Datum rezervace změněno!', 'success');

    }

    public function renderDefault()
    {
        $this->template->reservationCount = $this->reservationManager->getReservationCount();
        $this->template->reservations = $this->reservationManager->getReservations(0, 10);
    }

    public function bedsFormSucceded(Form $form, \stdClass $values)
    {
        $this->database->table('reservation')->get($this->getParameter('id'))->update(['beds' => $values->beds]);
        $this->flashMessage('Počet postelí změněn!', 'success');
    }

    protected function createComponentStatusForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->getElementPrototype()->setAttribute('data-ajax', 'true');
        $form->addSelect('status', 'Status:', $this->database->table('status')->fetchAssoc('id=value'));
        $form->addSubmit('send', 'Změnit');
        $form->setDefaults(['status' => $this->reservationManager->getReservation($this->getParameter('id'))->status_id]);
        $form->onSuccess[] = [$this, 'statusFormSucceded'];
        return $form;
    }

    protected function createComponentDateForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->getElementPrototype()->setAttribute('data-ajax', 'true');
        $form->addText('from', "Příjezd:")->setType("date")->setRequired('Prosím zadejte datum příjezdu');
        $form->addText('to', 'Odjezd:')->setType("date")->setRequired('Prosím zadejte datum odjezdu');
        $form->addSubmit('send', 'Změnit');
        $reservation = $this->reservationManager->getReservation($this->getParameter('id'));
        $form->setDefaults(['from' => date_format($reservation->start, 'Y-m-d'), 'to' => date_format($reservation->end, 'Y-m-d')]);
        $form->onSuccess[] = [$this, 'dateFormSucceded'];
        return $form;
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
