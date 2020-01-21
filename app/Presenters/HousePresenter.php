<?php


declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;

final class HousePresenter extends BasePresenter
{
    public function renderDefault(int $houseId): void
    {
        $house = $this->database->table('house')->get($houseId);
        if (!$house) {
            $this->error("Ubytování nenalezeno!");
        }
        $this->template->house = $house;
        $this->template->photos = $this->houseManager->getHousePhotos($houseId);
        $this->template->houses = $this->houseManager->getRandomHouses();
    }

    protected function createComponentReservationForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addText('from', "Příjezd:")->setType("date")->setRequired('Prosím zadejte datum příjezdu');
        $form->addText('to', 'Odjezd:')->setType("date")->setRequired('Prosím zadejte datum odjezdu');
        $form->addText('beds', 'Počet postelí')->setType('number')->setRequired('Prosím zadejte počet postelí');
        $form->addSubmit('send', 'Rezervovat');
        $form->onSuccess[] = [$this, 'reservationFormSucceeded'];
        return $form;
    }

    public function reservationFormSucceeded(Form $form, \stdClass $values)
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage("Error you dont belong here");
            $this->redirect('Homepage:default');
            exit;
        }

        $houseId = $this->getParameter('houseId');
        $this->database->beginTransaction();
        try {
            $this->database->table('reservation')->insert(['user_id' => $this->getUser()->getId(), 'house_idhouse' => $houseId, 'price' => 0, 'beds' => $values->beds, 'start' => $values->from, 'end' => $values->to, 'code' => "12345678"]);
            $this->database->commit();
        } catch (PDOException $e) {
            $this->database->rollBack();
            $this->flashMessage('Chyba!');
        }
        $this->redirect('default');

    }
}