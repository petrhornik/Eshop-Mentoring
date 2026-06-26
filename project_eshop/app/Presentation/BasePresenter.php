<?php

namespace App\Presentation;

use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    private Nette\Database\Explorer $database;

    public function injectDatabase(Nette\Database\Explorer $database): void
    {
        $this->database = $database;
    }

    public function beforeRender(): void
    {
        parent::beforeRender();

        $userId = $this->getUser()->getId();

        $cart_items_count = $userId === null
            ? 0
            : $this->database->table('cart_items')
                ->where('user_id', $userId)
                ->count('product_id');

        $this->template->activeUser = $this->getUser()->getIdentity();
        $this->template->isLoggedIn = $this->getUser()->isLoggedIn();
        $this->template->cart_items_count = $cart_items_count;
    }

    public function handleUserLogout(): void
    {
        $this->getUser()->logout(true);
        $this->flashMessage('User logged out.', 'success'); // dočíst si víc v docs
    }
}