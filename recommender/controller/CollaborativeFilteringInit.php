<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/UserItemRatingMatrix.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ItemFeatureSimComputation.php';
/**
* get item based and user based collaborative filtering prediction for user.
* initialise and run collaborative filtering engines
*/
class CollaborativeFilteringInit
{
  const PREDICTION_ALGORITHM_VARIANT = "A2";
  const RATING_MATRIX_TYPE = "not_nomalised";
  //admin runs recommender engine for all users
  public static function runUserBasedRecommendationEngine(){
    if(self::RATING_MATRIX_TYPE == "normalised"){
      $userItemRatingMatrix =UserItemRatingMatrix::normalisedMeanRatingMatrix("AllUser");
    }else{
      $userItemRatingMatrix =UserItemRatingMatrix::createRatingMatrix("AllUser",0);
    }
    $recommendations = array();
    $UserNearestNeigbour = array();
    $simAlgorithm = "PearsonCorrelation";
    $PredictionAlgoVariant = self:: PREDICTION_ALGORITHM_VARIANT;
    $updated_time = date("Y-m-d h:i:s", time());
    $uObj = new UserController();
    $Users = $uObj->getAllUser();
    foreach ($Users as $user) {

      if(array_key_exists($user['id'],$userItemRatingMatrix)){
        if($PredictionAlgoVariant == "A1"){
          $predicted_rating_Neigbour = UserBasedCFEngine::getPredict($simAlgorithm,$PredictionAlgoVariant,$userItemRatingMatrix, $user['id']);
          debugfilewriter("\n Prediction form Algorithm A1 Results\n");
        }else{
          $predicted_rating_Neigbour = UserBasedCFEngine::getPredict($simAlgorithm,$PredictionAlgoVariant,$userItemRatingMatrix, $user['id']);
          debugfilewriter("\n Prediction form Algorithm A2 Results\n");
        }
        $predicted_rating_Neigbour = explode('::',$predicted_rating_Neigbour);
        if(isset($predicted_rating_Neigbour[0])){
          $neibourhood_ranking = $predicted_rating_Neigbour[0];
          $UserNearestNeigbour[$user['id']] = $neibourhood_ranking;
        }
        if(isset($predicted_rating_Neigbour[1])){
          $recommendation = $predicted_rating_Neigbour[1];
          $recommendations[$user['id']] = $recommendation;
        }
      }

    }
    if(count($recommendations) > 0){
      $userPrediction = 'REPLACE INTO user_based_cf_recommendation VALUES';
      $query_parts = array();
      foreach ($recommendations as $userID => $prediction) {
        $query_parts[] = "('" . $userID . "', '" . $prediction . "', '" . $updated_time . "')";
      }
      $userPrediction .= implode(',', $query_parts);
      $PC = new PredictionController();
      $result = $PC->insertReplacePrediction($userPrediction); //insert or update prediction to database
    }
    if(count($UserNearestNeigbour) > 0){
      $nearestNeigbour = 'REPLACE INTO user_neigbourhood_ranks VALUES';
      $query_parts = array();
      foreach ($UserNearestNeigbour as $userID => $ranks) {
        $query_parts[] = "('" . $userID . "', '" . $ranks . "', '" . $updated_time . "')";
      }
      $nearestNeigbour .= implode(',', $query_parts);
      $PC = new PredictionController();
      $PC->insertReplacePrediction($nearestNeigbour); //insert or update prediction to database
      //update recommender last run time
      $sql =  "UPDATE `recommender_last_run` SET `usercf_last_updated` = ? WHERE `recommender_last_run`.`id` = 1";
      $PC->updateRecLastRun($sql);
      return $result;
    }
  }
  //compute item based recommender engine
  public static function runItemBasedRecommendationEngine(){
    if(self::RATING_MATRIX_TYPE == "normalised"){
      $userItemRatingMatrix =UserItemRatingMatrix::normalisedMeanRatingMatrix("AllUser");
    }else{
      $userItemRatingMatrix =UserItemRatingMatrix::createRatingMatrix("AllUser",0);
    }
    $recommendations = array();
    $updated_time = date("Y-m-d h:i:s", time());
    $uObj = new UserController();
    $Users = $uObj->getAllUser();
    foreach ($Users as $user) {
      if(array_key_exists($user['id'],$userItemRatingMatrix)){
        $userM = $userItemRatingMatrix[$user['id']];;
        $itemUserCF = ItemBasedCFEngine::computeItemBasedCF($user['id'],$userM,$userItemRatingMatrix);
      }
      $recommendations[$user['id']] = $itemUserCF;
    }
    $query = 'REPLACE INTO item_based_cf_recommendation VALUES';
    $query_parts = array();
    foreach ($recommendations as $userID => $prediction) {
      $query_parts[] = "('" . $userID . "', '" . $prediction . "', '" . $updated_time . "')";
    }
    $query .= implode(',', $query_parts);
    $PC = new PredictionController();
    $result = $PC->insertReplacePrediction($query); //insert or update prediction to database
    //update recommender last run time
    $sql =  "UPDATE `recommender_last_run` SET `itemcf_last_updated` = ? WHERE `recommender_last_run`.`id` = 1";
    $PC->updateRecLastRun($sql);
    return $result;
  }
}
