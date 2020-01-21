<?php


namespace App\Model;

use Nette;

class ReservationManager
{
    use Nette\SmartObject;
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function getReservationCount()
    {
        return $this->database->table('reservation')->count();
    }

    public function getReservations($offset, $limit)
    {
        return $this->database->table('reservation')->select('status.value,user.email,house.name,reservation.*')->joinWhere('status', 'status.id = reservation.status_id')->joinWhere('house', 'house.id = reservation.house_id')->joinWhere('user', 'user.id = reservation.user_id')->limit($limit, $offset);
    }

    public function getUserReservations($userId, $offset, $limit)
    {
        return $this->database->table('reservation')->select('status.value,user.email,house.name,reservation.*')->joinWhere('status', 'status.id = reservation.status_id')->joinWhere('house', 'house.id = reservation.house_id')->joinWhere('user', 'user.id = reservation.user_id')->where('reservation.user_id = ?', $userId)->limit($limit, $offset);
    }

    public function getReservationCountUser($userId)
    {
        return $this->database->table('reservation')->where('reservation.user_id = ?', $userId)->count();

    }

}