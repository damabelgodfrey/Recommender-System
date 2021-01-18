<?php
/**
* initialise and compute recommendations from user profile for all user
* fetch existing recommendation for current user
* @return product recommendation
*/
class ContentBasedInit {
  //admin runs recommender engine for all users
  public static function runContentBasedRecommendationEngine(){
    $recommendations = array();
    $updated_time = date("Y-m-d h:i:s", time());
    $upObj = new UserProfiler();
    $userProfiles = $upObj->getAllUserProfile();
    foreach ($userProfiles as $key => $profile) {
      $predicted_rating = ContentBasedRecommenderEngine::computeContentBasedPrediction($profile['userID'],$profile);
      if($predicted_rating !== ""){
        $recommendations[$profile['userID']] = $predicted_rating;
      }
    }
    $query = 'REPLACE INTO content_based_recommendation VALUES';
    $query_parts = array();
    foreach ($recommendations as $userID => $prediction) {
      $query_parts[] = "('" . $userID . "', '" . $prediction . "', '" . $updated_time . "')";
    }
    $query .= implode(',', $query_parts);
    $PC = new PredictionController();
    //insert or update all predictions to database
    $result = $PC->insertReplacePrediction($query);
    //update recommender last run time
    $sql =  "UPDATE `recommender_last_run` SET `cb_last_updated` = ? WHERE `recommender_last_run`.`id` = 1";
    $PC->updateRecLastRun($sql);
    return $result;
  }
}
