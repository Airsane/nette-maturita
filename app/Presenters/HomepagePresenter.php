<?php

declare(strict_types=1);

namespace App\Presenters;

final class  HomepagePresenter extends BasePresenter
{

    public function renderDefault()
    {
        if (!isset($this->template->houses))
            $this->template->houses = $this->houseManager->getHouses(0, 10);
    }
}
