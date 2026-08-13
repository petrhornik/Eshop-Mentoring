<?php

namespace App\Presentation\Seller;

use JetBrains\PhpStorm\NoReturn;
use Nette;
use App\Presentation\BasePresenter;
use Nette\Application\UI\Form;


final class SellerPresenter extends BasePresenter

{
    public function __construct(

    ){}

    //provede se mi před jakýmkoli .latte v presenteru + můžu udělat výjimky u konkrétních -> prevence accesu editorů kromě create Profile bez práv prodejce
    public function startup(): void     {
        parent::startup();

        $currAction = $this->getAction();

        $allowedActions = ['profileCreate'];

        $user = $this->getUser();

        if(!($user->isInRole('seller')) && !in_array($currAction, $allowedActions)) {
            $this->flashMessage('Váš účet nemá oprávnění pro tuto stránku! Pro tuto akci se prosím zaregistrujte jako prodejce...', "error");
            $this->redirect('User:edit');

        }

    }

    public function renderProfileCreate(): void
    {
        if ($this->getUser()->isInRole('seller')) {
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

    #[NoReturn]
    public function profileCreationFormSucceeded(Form $form, \stdClass $user): void {
        $this->redirect('SellerPortfolio:portfolio', $this->getUser()->getId());
    }

}
