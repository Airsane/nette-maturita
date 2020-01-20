<?php

declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model\HouseManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Image;
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

    public function renderShow(int $id): void
    {
        $house = $this->database->table('house')->get($id);
        if (!$house) {
            $this->error("Ubytování nenalezeno!");
        }
        $this->template->house = $house;
        $this->template->photos = $this->houseManager->getHousePhotos($id);
        $this['editForm']->setDefaults($house->toArray());
    }

    public function handleDelete(int $imgId): void
    {
        $data = $this->database->table('image')->get($imgId);
        $path = 'res/img/house/' . $data->name;
        unlink('res/img/house/thumb/' . $data->name);
        unlink($path);
        $this->database->table('image')->get($imgId)->delete();
    }

    public function handleDeleteHouse(int $houseId): void
    {
        $images = $this->database->table('image')->select('*')->where('house_id=?', $houseId)->fetchAll();
        foreach ($images as $image) {
            unlink('res/img/house/thumb/' . $image->name);
            unlink('res/img/house/' . $image->name);
        }
        $this->database->table('image')->where('house_id=?', $houseId)->delete();
        $this->database->table('house')->get($houseId)->delete();
    }

    public function handleSetDefault(int $imgId)
    {
        $this->database->query('UPDATE image SET isDefault = CASE WHEN id = ? THEN 1 ELSE 0 end WHERE house_id = ?', $imgId, $this->getParameter('id'));
    }

    public function createComponentImageForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addMultiUpload('photos');
        $form->addSubmit('send', 'Přidat obrázky');
        $form->onSuccess[] = [$this, 'imageFormSucceeded'];
        return $form;
    }

    public function imageFormSucceeded(Form $form, \stdClass $values)
    {
        $this->database->beginTransaction();
        try {
            $this->savePhotos($values->photos, $this->getParameter('id'));
            $this->database->commit();
        } catch (PDOException $e) {
            $this->database->rollBack();
            $this->flashMessage('Chyba!');
        }
    }

    public function createComponentEditForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addText('name', 'Název:')->setRequired('Prosím zadejte název');
        $form->addText('price', 'Cena:')->setRequired('Prosím zadejte cenu');
        $form->addText('beds', 'Počet osob:')->setRequired('Prosím zadejte počet osob');
        $form->addText('city', 'Město:')->setRequired('Prosím zadejte město');
        $form->addText('street', 'Ulice:')->setRequired('Prosím zadejte ulici');
        $form->addText('postcode', 'PSČ:')->setRequired('Prosím zadejte psč');
        $form->addTextArea('description', 'Popis:')->setHtmlId('summernote');
        $form->addSubmit('send', 'Upravit ubytování');
        $form->onSuccess[] = [$this, 'editFormSucceeded'];
        return $form;
    }

    public function editFormSucceeded(Form $form, array $values)
    {
        $houseId = $this->getParameter('id');
        if ($houseId) {
            $house = $this->database->table('house')->get($houseId);
            $house->update($values);
        }
        $this->redirect('default');
    }

    public function addFormSucceeded(Form $form, \stdClass $values)
    {
        $this->database->beginTransaction();
        try {
            $z = $this->database->table('house')->insert(['name' => $values->name, 'description' => $values->description, 'price' => $values->price, 'beds' => $values->price, 'city' => $values->city, 'street' => $values->street, 'postcode' => $values->postcode]);
            $this->savePhotos($values->photos, $z->id);
            $this->database->commit();
        } catch (PDOException $e) {
            $this->database->rollBack();
            $this->flashMessage('Chyba!');
        }
        $this->redirect('default');
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
                $image = Image::fromFile('res/img/house/' . $file_name);
                $image->resize(750, 450, Image::EXACT);
                $image->save('res/img/house/' . $file_name, 100, Image::PNG);
                $image->resize(300, 250, Image::EXACT);
                $image->save('res/img/house/thumb/' . $file_name, 100, Image::PNG);
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
