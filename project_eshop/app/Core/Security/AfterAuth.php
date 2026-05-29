<?php
/*
final class Authenticator implements
    Nette\Security\Authenticator, Nette\Security\IdentityHandler
{
    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $row = $this->db->fetch('SELECT * FROM user WHERE username = ?', $username);
        // ověříme heslo
        // ...
        // vrátíme identitu se všemi údaji z databáze
        return new SimpleIdentity($row->id, null, (array) $row);
    }

    public function sleepIdentity(IIdentity $identity): SimpleIdentity
    {
        // vrátíme zástupnou identitu, kde v ID bude authtoken
        return new SimpleIdentity($identity->authtoken);
    }

    public function wakeupIdentity(IIdentity $identity): ?SimpleIdentity
    {
        // zástupnou identitu nahradíme plnou identitou, jako v authenticate()
        $row = $this->db->fetch('SELECT * FROM user WHERE authtoken = ?', $identity->getId());
        return $row
            ? new SimpleIdentity($row->id, null, (array) $row)
            : null;
    }
}  */