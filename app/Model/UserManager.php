<?php


namespace App\Model;

use Nette;


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
        $row = $this->database->table('user')->select('password')->get($userid);
        if (!(new \Nette\Security\Passwords)->verify($oldpass, $row->password)) {
            return false;
        }
        $this->database->table('user')->get($userid)->update(['password' => (new \Nette\Security\Passwords)->hash($newpass)]);
        return true;
    }

    public function getUsers($userID, $offset, $limit)
    {
        return $this->database->fetchAll('SELECT * FROM user WHERE NOT id = ? LIMIT ?,?', $userID, $offset, $limit);
    }

    public function deleteUser($userId)
    {

    }

    public function setAdmin($userId)
    {
        $user = $this->database->table('user')->get($userId);
        $user->admin == 1 ? $user->update(array('admin' => 0)) : $user->update(array('admin' => 1));
    }

    public function getUserCount()
    {
        return $this->database->table('user')->count();
    }
}