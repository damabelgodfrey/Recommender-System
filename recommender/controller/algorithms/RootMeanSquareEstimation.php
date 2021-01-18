<?php

/**
 *
 */
class RootMeanSquareEstimation
{
  public static function getRatingProduct($productid,$predictedRating){
    $productObj = new ProductController();
    $productInf =$productObj->getProduct($productid);
    if(count($productInf) == 1){
      foreach ($productInf as $product) {
        $p_aveRating = $product['product_average_rating']; //average rating for product
      }
      $y = $predictedRating- $p_aveRating;
      $y1 = pow($y,2);
      return $y1;
    }
    return;
  }

  public static function computeRootMeanSqEst($itemRatingPredictionArray){
    $itemRatingPredictionRMQE = array();
    foreach ($itemRatingPredictionArray as $key => $value) {
      $p_id = $key;
      $rmqe = self::getRatingProduct($p_id,$value);
      $itemRatingPredictionRMQE[$p_id] = $rmqe;
    }
    if(count($itemRatingPredictionRMQE) != 0){
      $sum = array_sum($itemRatingPredictionRMQE)/count($itemRatingPredictionRMQE);
      $rmse =sqrt($sum);
      debugfilewriter("\nComputed Collaborative Filtering Root mean square estimation\n");
      debugfilewriter($rmse);
      return $rmse;
    }
   return -1; //no prediction

  }

}
