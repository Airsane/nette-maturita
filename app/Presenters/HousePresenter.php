<?php


declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;

final class HousePresenter extends BasePresenter
{
    /** @persistent */
    public $filter = [];


    public function renderSearch(string $data = "")
    {
        $this['filterForm']->setDefaults($this->filter);
        if (!isset($this->template->houses)) {
            $houses = $this->houseManager->findHouses($data, 0, 10);
            $this->template->houses = $this->applyFilters($houses, $this->filter);
        }
    }

    public function applyFilters($zaznam, $filtry)
    {
        if (isset($filtry['minB']) && isset($filtry['maxB'])) {
            $zaznam->where('beds BETWEEN ? AND ?', $filtry['minB'], $filtry['maxB']);
        } elseif (isset($filtry['minB'])) {
            $zaznam->where('beds >= ?', $filtry['minB']);
        } elseif (isset($filtry['maxB'])) {
            $zaznam->where('beds <= ?', $filtry['maxB']);
        }
        if (isset($filtry['minM']) && isset($filtry['maxM'])) {
            $zaznam->where('price BETWEEN ? AND ?', $filtry['minM'], $filtry['maxM']);
        } elseif (isset($filtry['minM'])) {
            $zaznam->where('price >= ?', $filtry['minM']);
        } elseif (isset($filtry['maxM'])) {
            $zaznam->where('price <= ?', $filtry['maxM']);
        }
        return $zaznam;
    }

    public function renderDefault(int $houseId): void
    {
        $house = $this->database->table('house')->get($houseId);
        if (!$house) {
            $this->error("Ubytování nenalezeno!");
        }
        $this->template->house = $house;
        $this->template->photos = $this->houseManager->getHousePhotos($houseId);
        $this->template->houses = $this->houseManager->getRandomHouses();
        $this->template->dates = $this->reservationManager->getReservationDates($houseId);
    }

    public function reservationFormSucceeded(Form $form, \stdClass $values)
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage("Error you dont belong here");
            $this->redirect('Homepage:default');
            exit;
        }

        $houseId = $this->getParameter('houseId');
        $reservation = $this->database->table('reservation')->where('start BETWEEN ? AND ? AND end BETWEEN ? AND ? ', $values->from, $values->to, $values->from, $values->to)->where('house_id = ? AND NOT status_id = 4', $this->getParameter('houseId'))->fetchAll();
        if (count($reservation) > 0) {
            $this->flashMessage('Na tento termín není rezervace možná!', 'danger');
            $this->redirect('this');
        }

        if (date('Y-m-d', strtotime($values->to)) <= date('Y-m-d', strtotime($values->from))) {
            $this->flashMessage('Datum odjezdu nemůže být dřívější/stejné než datum příjezdu!', 'danger');
            $this->redirect('this');
        }

        if (date('Y-m-d') > date('Y-m-d', strtotime($values->from))) {
            $this->flashMessage('Nemůžete si rezervovat v minulosti!', 'danger');
            $this->redirect('this');
        }

        $this->database->beginTransaction();
        try {
            $test = substr(md5(uniqid(strval(mt_rand()))), 0, 8);
            $this->database->table('reservation')->insert(['user_id' => $this->getUser()->getId(), 'house_id' => $houseId, 'beds' => $values->beds, 'start' => $values->from, 'end' => $values->to, 'code' => $test]);
            $this->database->commit();
            $this->flashMessage('Rezervace úspešně vytvořena!', 'success');
        } catch (PDOException $e) {
            $this->database->rollBack();
            $this->flashMessage('Chyba!', 'danger');
        }
        $this->redirect('this');
    }

    public function filterFormSucceded(Form $form, \stdClass $values)
    {
        $this->filter = array_filter((array)$values, function ($s) {
            return ($s === "" || $s === NULL || $s === [] ? FALSE : TRUE);
        });
        $this->template->houses = $this->applyFilters($this->houseManager->findHouses($this->getParameter('data'), 0, 10), $this->filter);
        if ($this->isAjax()) {
            $this->payload->url = $this->link('this');
            $this->redrawControl('Houses');
        } else {
            $this->redirect("this");
        }
    }


    protected function createComponentFilterForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addText('minB', "Min:")->setType("number");
        $form->addText('maxB', 'Max:')->setType("number");
        $form->addText('minM', "Min:")->setType("number");
        $form->addText('maxM', 'Max:')->setType("number");
        $form->addSubmit('send', 'Hledat');
        $form->onSuccess[] = [$this, 'filterFormSucceded'];
        return $form;
    }

    protected function createComponentReservationForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $house = $this->database->table('house')->get($this->getParameter('houseId'));
        $form->addText('from', "Příjezd:")->setType("date")->setRequired('Prosím zadejte datum příjezdu')->setHtmlAttribute('min', date('Y-m-d'))->setDefaultValue(date('Y-m-d'));
        $form->addText('to', 'Odjezd:')->setType("date")->setRequired('Prosím zadejte datum odjezdu')->setHtmlAttribute('min', date('Y-m-d', strtotime(date('Y-m-d') . "+1 days")))->setDefaultValue(date('Y-m-d', strtotime(date('Y-m-d') . "+1 days")));
        $form->addText('beds', "Počet postelí:")->setType("number")->setRequired('Prosím zadejte počet postelí')->addRule(Form::INTEGER, 'Počet postelí musí být číslo')->addRule(Form::RANGE, 'Pocet postelí musí být od %d do %d', [1, $house->beds]);
        $form->addText('cena', 'Cena')->setDisabled(true)->setHtmlId('priceCalc')->setDefaultValue(number_format($house->price, 0, ',', ' ') . ' Kč');
        $form->addSubmit('send', 'Rezervovat');
        $form->onSuccess[] = [$this, 'reservationFormSucceeded'];
        return $form;
    }

}