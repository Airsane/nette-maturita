<?php
/**
 * Created by PhpStorm.
 * User: patri
 * Date: 27.03.2019
 * Time: 9:01
 */

namespace App\Model;

use Nette;
use Nette\Security as NS;


class MyAuthenticator implements NS\IAuthenticator
{
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function authenticate(array $credentials): NS\IIdentity
    {
        list($email, $password) = $credentials;
        $row = $this->database->table('user')
            ->where('email', $email)->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('User not found.');
        }

        if (!(new \Nette\Security\Passwords)->verify($password, $row->password)) {
            throw new Nette\Security\AuthenticationException('Invalid password.');
        }
        return new Nette\Security\Identity($row->id, $row->admin, ['email' => $row->email, 'firstname' => $row->firstname, 'lastname' => $row->lastname]);
    }
}