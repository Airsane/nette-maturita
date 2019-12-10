<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model\HouseManager;
use Nette;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;


final class HousePresenter extends Nette\Application\UI\Presenter
{
    private $database;
    private $houseManager;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->houseManager = new HouseManager($database);
    }

    public function renderDefault()
    {
        $this->template->houseCount = $this->houseManager->getHouseCount();
        $this->template->houses = $this->houseManager->getHouses(0, 10);
    }

    public function addFormSucceeded(Form $form, \stdClass $values)
    {
        $this->database->beginTransaction();
        try {
            $z = $this->database->table('house')->insert(['name' => $values->name, 'description' => $values->description, 'price' => $values->price, 'beds' => $values->price, 'city' => $values->city, 'street' => $values->street, 'postcode' => $values->postcode]);
            $this->savePhotos($values->photos, $z->idhouse);
            $this->database->commit();
        } catch (PDOException $e) {
            $this->database->rollBack();
            $this->flashMessage('Chyba!');
        }
    }

    public function savePhotos($images, $idd)
    {
        foreach ($images as $key => $file) {
            if ($file->isImage() and $file->isOk()) {
                $id = uniqid();
                $file_ext = strtolower(mb_substr($file->getSanitizedName(), strrpos($file->getSanitizedName(), ".")));
                $file_name = $id . '.png';
                if ($file_ext != '.png') {
                    imagepng(imagecreatefromstring(file_get_contents($_FILES["photos"]["tmp_name"][$key])), 'res/img/house/' . $file_name);
                    $this->database->query('INSERT INTO image (house_id,name,isDefault) VALUES (?,?,?)', $idd, $file_name, $key == 0 ? 1 : 0);
                } else {
                    $file->move('res/img/house/' . $file_name);
                    $this->database->query('INSERT INTO image (house_id,name,isDefault) VALUES (?,?,?)', $idd, $file_name, $key == 0 ? 1 : 0);

                }
            }
        }
    }

    protected function createComponentAddForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addText('name', 'Název:')->setRequired('Prosím zadejte název');
        $form->addText('price', 'Cena:')->setRequired('Prosím zadejte cenu');
        $form->addText('beds', 'Počet osob:')->setRequired('Prosím zadejte počet osob');
        $form->addText('city', 'Město:')->setRequired('Prosím zadejte město');
        $form->addText('street', 'Ulice:')->setRequired('Prosím zadejte ulici');
        $form->addText('postcode', 'PSČ:')->setRequired('Prosím zadejte psč');
        $form->addMultiUpload('photos');
        $form->addTextArea('description', 'Popis:')->setHtmlId('summernote');
        $form->addSubmit('send', 'Přidat ubytování');
        $form->onSuccess[] = [$this, 'addFormSucceeded'];
        return $form;
    }

}
