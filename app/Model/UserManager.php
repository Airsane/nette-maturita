<?php


namespace App\Model;

use Nette;
use Nette\Security\Passwords;


class UserManager
{
    use Nette\SmartObject;
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function changePassword($oldpass, $newpass, $userid)
    {
        $row = $this->database->table('users')->select('password')->get($userid);
        if (!Nette\Security\Passwords::verify($oldpass, $row->password)) {
            return false;
        }
        $this->database->table('users')->get($userid)->update(['password' => Passwords::hash($newpass)]);
        return true;
    }

    public function getUser($id)
    {
        return $this->database->fetch('SELECT * FROM user where id = ?', $id)->fetch();
    }

    public function getUsers($offset, $limit)
    {
        return $this->database->fetchAll('SELECT * FROM user LIMIT ?,?', $offset, $limit);
    }

    public function getUserCount()
    {
        return $this->database->table('user')->count();
    }
}