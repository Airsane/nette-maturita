<?php


namespace App\Model;


use Nette;


class HouseManager
{
    use Nette\SmartObject;
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getHouse($id)
    {
        return $this->database->fetch('SELECT * FROM house where id = ?', $id);
    }

    public function getHousePhotos($id)
    {
        return $this->database->fetchAll('SELECT * FROM image where house_id = ?', $id);
    }

    public function getHousePhoto($id)
    {
        return $this->database->fetch('SELECT * FROM image where house_id = ? and isDefault = 1', $id);
    }

    public function getHouses($offset, $limit)
    {
        return $this->database->table('house')->select('house.*')->select(':image.name AS "image"')->where(':image.isDefault=1')->limit($limit, $offset);
    }

    public function getRandomHouses()
    {
        return $this->database->table('house')->select('house.*')->select(':image.name AS "image"')->where(':image.isDefault=1')->order('RAND()')->limit(3);
    }

    public function getHouseCount()
    {
        return $this->database->table('house')->count();
    }

    public function findHouses($data, $offset, $limit)
    {
        return $this->database->table('house')->select('house.*')->where('city LIKE "' . $data . '%"')->select(':image.name AS "image"')->where(':image.isDefault=1')->limit($limit, $offset);
    }

    public function countHouses($data)
    {
        return $this->database->table('house')->select('house.*')->where('city LIKE "' . $data . '%"');
    }

    public function getComments($houseId)
    {
        return $this->database->table('comment')->select('comment.*,user.firstname,user.lastname')->joinWhere('user', 'user.id = comment.user_id')->where('house_id = ?', $houseId);
    }
}