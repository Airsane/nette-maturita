<?php


declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;

final class HousePresenter extends BasePresenter
{
    /** @persistent */
    public $filter = [];

    /** @persistent */
    public $data;
    /** @persistent */
    public $page = 1;

    /** @persistent */


    public function renderSearch(int $page = 1, string $data = "")
    {
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemsPerPage(10);
        $paginator->setPage($page);
        $this['filterForm']->setDefaults($this->filter);
        if (!isset($this->template->houses)) {
            $houses = $this->houseManager->findHouses($data, $paginator->getOffset(), $paginator->getLength());
            $houses = $this->applyFilters($houses, $this->filter);
            $housesCount = $this->applyFilters($this->houseManager->countHouses($data), $this->filter);
            $this->template->houses = $houses;
            $paginator->setItemCount($housesCount->count());
        }
        $this->template->paginator = $paginator;
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
        $this->template->comments = $this->houseManager->getComments($houseId);
        $this->template->rating = $this->database->table('star')->select('SUM(rate)/Count(*)/5*100 AS rate')->where('house_id = ?', $houseId)->fetch();
        if ($this->user->isLoggedIn()) {
            $this->template->ratingUser = $this->database->table('star')->where('user_id = ? AND house_id = ?', $this->user->getId(), $houseId)->fetch();
        }
    }

    public function handleRatingChange($house_id, $rating)
    {
        if ($this->user->isLoggedIn()) {
            if ($rating < 1 || $rating > 5) {
                $this->flashMessage('Chyba neplatná hodnota!', 'danger');
                $this->redirect('this');
                exit;
            }
            $rate = $this->database->table('star')->where('user_id = ? AND house_id = ? ', $this->user->getId(), $house_id);
            if ($rate->count() > 0) {
                $rate->update(['rate' => $rating]);
                $this->redirect('this');
            }
            $this->database->table('star')->insert(['house_id' => $house_id, 'user_id' => $this->user->getId(), 'rate' => $rating]);
            $this->redirect('this');
        }
    }

    public function reservationFormSucceeded(Form $form, \stdClass $values)
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage("Error you dont belong here");
            $this->redirect('Homepage:default');
            exit;
        }

        $houseId = $this->getParameter('houseId');
        $reservation = $this->database->table('reservation')->where('start BETWEEN ? AND ? AND end BETWEEN ? AND ? ', date('Y/m/d', strtotime($values->from)), date('Y/m/d', strtotime($values->to)), date('Y/m/d', strtotime($values->from)), date('Y/m/d', strtotime($values->to . ' + 1 days')))->where('house_id = ? AND NOT status_id = 4', $this->getParameter('houseId'))->fetchAll();
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
            $code = substr(md5(uniqid(strval(mt_rand()))), 0, 8);
            $res = $this->database->table('reservation')->insert(['user_id' => $this->getUser()->getId(), 'house_id' => $houseId, 'beds' => $values->beds, 'start' => $values->from, 'end' => $values->to, 'code' => $code]);
            $this->database->commit();
            $mail = new Message;
            $email = $this->database->table('user')->get($this->getUser()->getId())->email;
            $mail->setFrom('Kratos <info@kabelepa.spse-net.cz>')
                ->addTo($email)
                ->setSubject('Potvrzení rezervace')
                ->setBody("Dobrý den,\nprosím potvrďte rezervaci tímto odkazem https://kabelepa.mp.spse-net.cz/www/reservation/confirm?resId=" . $res->id . "&code=" . $code . "");
            $mailer = new SendmailMailer;
            $mailer->send($mail);
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

    public function reviewFormSucceded(Form $form, \stdClass $values)
    {
        if (!$this->user->isLoggedIn()) {
            $this->flashMessage("Chyba nepatříš sem!", 'danger');
            $this->redirect('Homepage:default');
            exit;
        }

        $this->database->table('comment')->insert(['house_id' => $values->house_id, 'user_id' => $this->getUser()->getId(), 'text' => $values->text]);
        $this->flashMessage('Hodnocení přidáno!', 'success');
        $this->redirect('this');
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

    protected function createComponentReviewForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $reservation = $this->reservationManager->getReservation($this->getParameter('id'));
        $form->addTextArea('text', 'Komentář:')->addRule(Form::MAX_LENGTH, 'Maximální počet znaků je %d', 250);
        $form->addHidden('house_id', $this->getParameter('houseId'));
        $form->addSubmit('send', 'Přidat hodnocení');
        $form->onSuccess[] = [$this, 'reviewFormSucceded'];
        return $form;
    }

}