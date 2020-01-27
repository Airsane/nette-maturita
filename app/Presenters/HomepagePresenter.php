<?php

declare(strict_types=1);

namespace App\Presenters;
use Nette;
use Tomaj\Form\Renderer\BootstrapVerticalRenderer;

final class HomepagePresenter extends BasePresenter
{

    public function renderDefault()
    {
        if (!isset($this->template->houses))
            $this->template->houses = $this->houseManager->getHouses(0, 10);
    }

    protected function createComponentSearchForm()
    {
        $form = new Nette\Application\UI\Form;
        $form->setRenderer(new BootstrapVerticalRenderer);
        $form->addText('search', 'Hledat');
        $form->addSubmit('send', 'Hledat');
        $form->onSuccess[] = function ($form, $values) {
            $this->template->houses = $this->houseManager->getRandomHouses();
            $this->redrawControl('test');
        };
        return $form;
    }

    public function searchFormSucceeded($form, $values)
    {

    }

}
