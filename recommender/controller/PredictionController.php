<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DBh.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/UserController.php';
/**
 *
 */
class PredictionController extends DBh{
  //get prediction from database
  public function getPrediction($type,$userID){
    if($type == "ContentBased"){
      $sql = "SELECT * FROM content_based_recommendation WHERE userID = ?";

    }elseif($type == "UserBasedCF"){
      $sql = "SELECT * FROM user_based_cf_recommendation WHERE userID = ?";

    }elseif($type == "ItemBasedCF"){
      $sql = "SELECT * FROM item_based_cf_recommendation WHERE userID = ?";
    }else{
      $sql = "SELECT * FROM item_based_cf_recommendation WHERE userID = ?";
    }
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$userID]);
    $results = $myQuerry->fetchAll();
    return $results;
  }
  public function getContentBasedRecommendation($user_id){
    $contentBasedP = array();
    $prediction = $this->getPrediction("ContentBased",$user_id);
    foreach ($prediction as $predict) {
      $predictions = json_decode($predict['cb_prediction'],true);
      foreach ($predictions as $key => $prediction){
        $contentBasedP[$prediction['product_id']] = $prediction['similarity'];
      }
    }
    return $contentBasedP;
  }
  //get user prediction
  public function getItemBasedCFRecommendation($user_id){
    $itemUserCF = array();
    $prediction = $this->getPrediction("ItemBasedCF",$user_id);
    if(count($prediction)==1){
      foreach ($prediction as $predict) {
        $predictions = json_decode($predict['item_cf_prediction'],true);
        foreach ($predictions as $key => $prediction){
          $itemUserCF[$prediction['product_id']] = $prediction['predicted_rating'];
        }
      }
    }
    return $itemUserCF;
  }
  //get user based prediction
  public function getUserBasedCFRecommendation($user_id){
    $finalPredictionArray = array();
    $prediction = $this->getPrediction("UserBasedCF",$user_id);
    if(count($prediction)==1){
      foreach ($prediction as $predict) {
        $predictions = json_decode($predict['user_cf_prediction'],true);
        foreach ($predictions as $key => $prediction){
          $finalPredictionArray[$prediction['product_id']] = $prediction['predicted_rating'];
        }
      }
    }
    return $finalPredictionArray;
  }
  //inserts/update user-user, item-usre collaborative filtering computation to database
  //insert item prediction and user neibourhood ranking.
  public function insertReplacePrediction($query){
    $myQuerry = $this->getConnection()->prepare($query);
    $results = $myQuerry->execute();
    return $results;
  }

  public function getAllPrediction($type){
    if($type == "ContentBased"){
      $sql = "SELECT * FROM content_based_recommendation";

    }elseif($type == "UserBasedCF"){
      $sql = "SELECT * FROM user_based_cf_recommendation";

    }elseif($type == "ItemBasedCF"){
      $sql = "SELECT * FROM item_based_cf_recommendation";
    }else{
      $sql = "SELECT * FROM item_based_cf_recommendation";
    }
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute();
    $results = $myQuerry->fetchAll();
    return $results;
  }
  public function getLastRecommenderEngineRun(){
    $sql = "SELECT * FROM recommender_last_run";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute();
    $results = $myQuerry->fetchAll();
    return $results;
  }
  public function updateRecLastRun($query){
    $myQuerry = $this->getConnection()->prepare($query);
    $results = $myQuerry->execute([date("Y-m-d")]);
    return $results;
  }
}
