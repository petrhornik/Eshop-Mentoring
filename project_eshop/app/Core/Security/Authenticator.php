<?php

namespace App\Core\Security;

use Nette;
use Nette\Security\SimpleIdentity;

class Authenticator implements Nette\Security\Authenticator
{
    public function __construct(
        private Nette\Database\Explorer  $database,
        private Nette\Security\Passwords $passwords,
    )
    {

    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $row = $this->database->table('users')
            ->where('name', $username)
            ->fetch();

        if (!$row) {
            echo '<script>alert("User not found.");</script>'; //smazat
           throw new Nette\Security\AuthenticationException('User not found.');

        }

        if (!$this->passwords->verify($password, $row->password)) {
            echo '<script>alert("Wrong password.");</script>'; //smazat
            echo $row->password;
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }

        return new SimpleIdentity(
            $row->id,
            [],                     //dodělat import rolí z "roles" přes "user_roles" (claude)
            ['name' => $row->name],
        );
    }
}
