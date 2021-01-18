<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DBh.php';
/**
 *  Carry out all rating CRUD operation
 */
class Ratings extends DBh{

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
    $myQuerry->execute([$user_id,$updated_time,$rating_json]);
  }

//update user ratings
  protected function updateRatings($sql,$rating_json,$updated_time, $userID){
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$rating_json, $updated_time, $userID]);
  }
}
