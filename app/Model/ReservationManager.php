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
        return $this->database->table('reservation')->select('status.value,user.email,house.name,house.price,reservation.*, DATEDIFF(reservation.end, reservation.start) AS DateDiff')->joinWhere('status', 'status.id = reservation.status_id')->joinWhere('house', 'house.id = reservation.house_id')->joinWhere('user', 'user.id = reservation.user_id')->where('reservation.user_id = ?', $userId)->limit($limit, $offset);
    }

    public function getReservationCountUser($userId)
    {
        return $this->database->table('reservation')->where('reservation.user_id = ?', $userId)->count();
    }

    public function getReservation($id)
    {
        return $this->database->table('reservation')->select('status.value,user.email,user.firstname,user.lastname,house.name,house.price,reservation.*,DATEDIFF(reservation.end, reservation.start) AS DateDiff')->joinWhere('status', 'status.id = reservation.status_id')->joinWhere('house', 'house.id = reservation.house_id')->joinWhere('user', 'user.id = reservation.user_id')->get($id);
    }

    public function getUserAveragePrice($id)
    {
        return $this->database->query('SELECT AVG(house.price * DATEDIFF(reservation.end, reservation.start)) as price FROM house JOIN reservation on reservation.house_id = house.id JOIN user on user.id = reservation.user_id WHERE user.id = ?', $id)->fetch();
    }

    public function getUserAverageBeds($id)
    {
        return $this->database->query('SELECT AVG(reservation.beds) as beds FROM reservation JOIN user on user.id = reservation.user_id WHERE user.id = ?', $id)->fetch();
    }

    public function getHouseReservations($houseId, $offset, $limit)
    {
        return $this->database->table('reservation')->select('status.value,user.email,house.name,reservation.*')->joinWhere('status', 'status.id = reservation.status_id')->joinWhere('house', 'house.id = reservation.house_id')->joinWhere('house', 'house.id = reservation.house_id')->where('reservation.house_id = ?', $houseId)->limit($limit, $offset);
    }

    public function getReservationCountHouse($houseId)
    {
        return $this->database->table('reservation')->where('reservation.house_id = ?', $houseId)->count();
    }

    public function getReservationDates($houseId)
    {
        return $this->database->table('reservation')->where('house_id = ? AND NOT status_id = 4', $houseId);
    }

}