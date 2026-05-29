<?php

namespace App\Presentation;

use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->template->activeUser = $this->getUser()->getIdentity();
        $this->template->isLoggedIn = $this->getUser()->isLoggedIn();

    }

    public function handleUserLogout(): void
    {
        $this->getUser()->logout();
        $this->flashMessage('User logged out.', 'success'); // dočíst si víc v docs
    }
}