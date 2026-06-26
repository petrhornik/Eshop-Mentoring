<?php

namespace App\Presentation\Seller;

use Nette;
use App\Presentation\BasePresenter;
use Nette\Application\UI\Form;


final class SellerPresenter extends BasePresenter

{
    public function __construct(

    ){}

    public function renderProfileCreate(): void
    {
        if (!$this->getUser()->isInRole('seller')) {
            $this->flashMessage('Na tuto stránku nemůžete přistoupit. Zkuste se přihlásit jinným účtem či se přihlaste.', 'danger');
            $this->redirect('User:login');
        }

        $this->template->user = $this->getUser();
    }

    protected function createComponentProfileCreationForm(): Form {
        $form = new Form;

        $form->addText('uname', 'Username:')
            ->setRequired('Zadej uživatelské jméno.');

        $form->addPassword('password', 'Password:')
            ->setRequired('Zadej heslo.');

        $form->addSubmit('login', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }
}
