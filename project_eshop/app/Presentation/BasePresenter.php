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

        $cartItems = $this->database->table('cart_items')
            ->where('user_id', $userId);

        $cartItemsCount = $userId === null
            ? 0
            : $this->database->table('cart_items')
                ->where('user_id', $userId)
                ->sum('quantity');

        $cartItemsCount = $cartItemsCount === null ? 0 : $cartItemsCount;

        $this->template->user = $this->getUser();
        $this->template->isLoggedIn = $this->getUser()->isLoggedIn();
        $this->template->cartItems = $cartItems;
        $this->template->cartItemsCount = $cartItemsCount;

    }

    public function handleUserLogout(): void
    {
        $this->getUser()->logout(true);
        $this->flashMessage('User logged out.', 'success'); // dočíst si víc v docs
    }
}