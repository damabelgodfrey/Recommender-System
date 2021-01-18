<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DB_PDO.php';
/**
 *  Carry out all rating CRUD operation
 */
class Ratings extends DB_PDO{

// get the rating of a user
  public function getRatings(&$userID) : array{
    $sql = "SELECT * FROM ratings WHERE userID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$userID]);
    $results = $myQuerry->fetchAll();
    return $results;
  }
  // Fetch all ratings
  public function getAllRatings() : array{
    $sql = "SELECT * FROM ratings";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute();
    $results = $myQuerry->fetchAll();
    return $results;
  }

//add rating to database
  protected function setRatings($sql,$user_id,$updated_time,$rating_json){
    $myQuerry = $this->getConnection()->prepare($sql);
    $result = $myQuerry->execute([$user_id,$updated_time,$rating_json]);
    return $result;
  }

//update user ratings
  protected function updateRatings($sql,$rating_json,$updated_time, $userID){
    $myQuerry = $this->getConnection()->prepare($sql);
    $result = $myQuerry->execute([$rating_json, $updated_time, $userID]);
    return $result;
  }
  public function getAvProductRatings($id){
    $sql = "SELECT * FROM average_ratings WHERE id = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$id]);
    $results = $myQuerry->fetchAll();
    return $results;
  }
}
