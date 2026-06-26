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

        $roles = [];

        try {
            foreach ($row->related('user_roles') as $user_role) {
                $role = $user_role->ref('roles', 'role_id');

                $roles[] = $role->name;
            }
        }   catch (\Exception $e) {
            echo '<script>alert("User does not have any roles inside database, please contact administrator or support!!");</script>';
            throw new Nette\Security\AuthenticationException('Database profile error. Please contact customer support.');
        }

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
            $roles,                     //dodělat import rolí z "roles" přes "user_roles" (claude)
            ['name' => $row->name],
        );
    }
}
