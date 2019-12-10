<?php


declare(strict_types=1);

namespace App\Presenters;


final class HousePresenter extends BasePresenter
{
    public function renderShow(int $id): void
    {
        $house = $this->database->table('house')->get($id);
        if (!$house) {
            $this->error("Ubytování nenalezeno!");
        }
        $this->template->house = $house;
        $this->template->photos = $this->houseManager->getHousePhotos($id);
    }
}