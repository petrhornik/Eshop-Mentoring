<?php

namespace App\Presentation\User;

use Nette;
use Nette\Application\UI\Form;
use App\Presentation\BasePresenter;

final class UserPresenter extends BasePresenter
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

            $user = $this->database->table('users')->where('name', $data->uname)->fetch();

            $this->database->table('user_roles')->insert([
                'user_id' => $user->id,
                'role_id' => 1,
            ]);

            echo '<script>alert("Uživatel úspěšně vytvořen, děkujeme!")</script>';


        } catch (Nette\Database\UniqueConstraintViolationException $e){
            $form->addError('Uživatel již existuje.');
        } catch (Nette\Database\Exception $e) {
            $form->addError("Nastala chyba při vytváření vašeho účtu, zkuste to prosím později...");
        }
    }

    public function renderEdit(): void {

        if (!$this->getUser()->isLoggedIn()){
            $this->flashMessage('Pro použití editoru musí být uživatel přihlášen!', 'danger');
            $this->redirect('User:login');
        }

        $this->template->user = $this->getUser();

    }

    protected function createComponentUserChangeCredentialsForm(): Form {
        $form = new Form;

        $form->addText('uname', 'Username:')
            ->setRequired('Zadej uživatelské jméno.');

        $form->addEmail('email', 'Email:')
            ->setRequired('Vyplňte prosím email..');

        $form->addPassword('password', 'Ověření heslem:')
            ->setRequired('Zadej heslo.');

        $form->addSubmit('submit', 'Změnit');

        $form->onSuccess[] = [$this, 'userCHangeCredentialsFormSucceeded'];

        return $form;
    }

    protected function createComponentUserChangePassForm(): Form {
        $form = new Form;

        $form->addText('oldPass', 'Původní heslo:')
            ->setRequired('Zadejte vaše staré heslo.');

        $form->addEmail('newPass', 'Vyplňte nové heslo:')
            ->setRequired('Vyplňte nové heslo.');

        $form->addPassword('newPassVerify', 'Ověření:')
            ->setRequired('Zadeje nové heslo znovu.');

        $form->addSubmit('submit', 'Změnit');

        $form->onSuccess[] = [$this, 'userChangePassFormSucceeded'];

        return $form;
    }

    public function userChangeCredentialsFormSucceeded(Form $form, \stdClass $data): void {
        $currUser = $this->database->table('users')->where('email', $data->email)->fetch();
        $dbPassHash = $currUser->password;

        if($this->passwords->verify($data->oldPass, $dbPassHash)){
            $this->database->table('users')
                ->where('id', $this->getUser()->getId())
                ->update([
                    'uname' => $data->uname,
                    'email' => $data->email,
                ]);
        }


    }

    public function userChangePassFormSucceeded(Form $form, \stdClass $user): void {

    }

    public function handleSellerStatusToggle(): void {

    }
}