<?php

/**
* Compute prediction accuracy AccuracyTestMetric
*@ return RMSE
*/
class AccuracyTestMetric
{
  public static function averageRating($currentUser,$matrix){
    $currentItem_metrix =array();
    $result = 0;
    //  $otherItem_metrix = array();
    $userMatrix = array();
    if(array_key_exists($currentUser, $matrix)){
      $userMatrix = $matrix[$currentUser];
      foreach ($userMatrix as $key => $value) { //check if user has rated a product also rated by user2
        $currentItem_metrix[$key] = $value;
      }
      $p_aveRating = array_sum($currentItem_metrix)/count($currentItem_metrix);
    }
    return $p_aveRating;
  }

  public static function computeRootMeanSqEst($currentUser,$matrix,$itemRatingPredictionArray){
    $itemRatingPredictionRMQE = array();
    $p_aveRating = self::averageRating($currentUser,$matrix);
    foreach ($itemRatingPredictionArray as $key => $predictedRating) {
      $y = $predictedRating- $p_aveRating;
      $y1 = pow($y,2);
      $itemRatingPredictionRMQE[$currentUser] = $y1;
    }
    if(count($itemRatingPredictionRMQE) != 0){
      $sum = array_sum($itemRatingPredictionRMQE)/count($itemRatingPredictionRMQE);
      $rmse =sqrt($sum);
      return $rmse;
    }
    return 0; //no prediction
  }

}
