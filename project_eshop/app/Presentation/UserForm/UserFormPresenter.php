<?php

namespace App\Presentation\UserForm;

use Nette;
use Nette\Application\UI\Form;
use App\Presentation\BasePresenter;

final class UserFormPresenter extends BasePresenter
{

    public function __construct(
        private Nette\Database\Explorer $database,
        private Nette\Security\Passwords $passwords,
    ) {
        parent::__construct(); // toto tu musi byt jinak to nepojede :D
    }

    protected function createComponentLoginForm(): Form
    {
        $form = new Form;

        $form->addText('uname', 'Username:')
            ->setRequired('Zadej uživatelské jméno.');

        $form->addPassword('password', 'Password:')
            ->setRequired('Zadej heslo.');

        $form->addSubmit('login', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'loginFormSucceeded'];

        return $form;
    }

    protected function createComponentRegisterForm(): Form
    {
        $form = new Form;
        $form->addText('uname', 'Username:')
            ->setRequired("Potřebujete uživatelské jméno!");
        $form->addEmail('email', 'Email:')
            ->setRequired("Potřebujeme váš email!");
        $form->addPassword('password', 'Password:')
            ->setRequired("Nastavte si heslo.");
        $form->addCheckbox('agree_terms', 'Souhlasím se zpracováním údajů.')
            ->setRequired("Pro registraci je toto povinné!");
        $form->addSubmit('login', 'Přihlásit se');

        $form->onSuccess[] = [$this, 'registerFormSucceeded'];

        return $form;
    }

    public function loginFormSucceeded(Form $form, \stdClass $data): void
    {
        try {
            $this->getUser()->login($data->uname, $data->password);

            $this->redirect('Home:default'); // přesměruj po přihlášení
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Špatné jméno nebo heslo.');
        }
    }

    public function registerFormSucceeded(Form $form, \stdClass $data): void
    {
        try{
            $this->database->table('users')->insert([
                'name' => $data->uname,
                'email' => $data->email,
                'password' => $this->passwords->hash($data->password),
            ]);

            echo '<script>alert("Uživatel úspěšně vytvořen!")</script>';

            //$user = $this->database->table('users')
            //    ->order('id DESC')
            //    ->limit(1);


        } catch (Nette\Database\UniqueConstraintViolationException $e){
            $form->addError('Uživatel již existuje.');
        } catch (Nette\Database\Exception $e) {
            $form->addError("Nastala chyba při vytváření vašeho účtu, zkuste to prosím později...");
        }
    }
}