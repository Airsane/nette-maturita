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

    public function getHouses($offset, $limit)
    {
        return $this->database->fetchAll('SELECT * FROM house LIMIT ?,?', $offset, $limit);
    }

    public function getHouseCount()
    {
        return $this->database->table('house')->count();
    }
}