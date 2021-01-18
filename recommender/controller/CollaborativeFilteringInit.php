<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/UserItemRatingMatrix.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ParserFunctions.php';
/**
* get item based and user based collaborative filtering prediction for user.
* initialise and run collaborative filtering engines
*/
class CollaborativeFilteringInit
{
  const PREDICTION_ALGORITHM_VARIANT = "P2";
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
    foreach ($userItemRatingMatrix as $userID => $value) {
        $predicted_rating_Neigbour = UserBasedCFEngine::getPredict($simAlgorithm,$PredictionAlgoVariant,$userItemRatingMatrix, $userID);
      $predicted_rating_Neigbour = explode('::',$predicted_rating_Neigbour);
      if(isset($predicted_rating_Neigbour[0])){
        $neibourhood_ranking = $predicted_rating_Neigbour[0];
        $UserNearestNeigbour[$userID] = $neibourhood_ranking;
      }
      if(isset($predicted_rating_Neigbour[1])){
        $recommendation = $predicted_rating_Neigbour[1];
        $recommendations[$userID] = $recommendation;
      }
    }
    debugfilewriter("\nNearest neighbor and Similarity Score based on" .$simAlgorithm."\n");
    debugfilewriter($UserNearestNeigbour);
    debugfilewriter("\nFinal Item Prediction Results\n");
    debugfilewriter($recommendations);
    $result = false;
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
    foreach ($userItemRatingMatrix as $userID => $value) {
      $userM = $userItemRatingMatrix[$userID];;
      $itemUserCF = ItemBasedCFEngine::computeItemBasedCF($userID,$userM,$userItemRatingMatrix);
      $recommendations[$userID] = $itemUserCF;
    }
    debugfilewriter("\nFinal Results");
    debugfilewriter($recommendations);
    $query = 'REPLACE INTO item_based_cf_recommendation VALUES';
    $query_parts = array();
    foreach ($recommendations as $userID => $prediction) {
      $query_parts[] = "('" . $userID . "', '" . $prediction . "', '" . $updated_time . "')";
    }
    $result =0;
    $query .= implode(',', $query_parts);
    $PC = new PredictionController();
  $result = $PC->insertReplacePrediction($query); //insert or update prediction to database
    //update recommender last run time
    $sql =  "UPDATE `recommender_last_run` SET `itemcf_last_updated` = ? WHERE `recommender_last_run`.`id` = 1";
    $PC->updateRecLastRun($sql);
    return $result;
  }
}
