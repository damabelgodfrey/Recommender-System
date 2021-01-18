<?php
/**
* Compute hybrid recommender by taking weighted value of the prediction output from all
* recommender engine.
*/
class HybridRecommenderEngine
{
  const ITEMBASED_CF_WEIGHT = 0.3;
  const USERBASED_CF_WEIGHT = 0.3;
  const CONTENT_WEIGHT = 0.4;
  public static function runHybridRecommendationEngine(){
    $prediction = new PredictionController();
    $AllPredictions = $prediction->getAllCombinePrediction();
    $pObj = new ProductController();
    $allProduct = $pObj->getAllProducts();
    $itemIDs = array();
    $userBasedCF = array();
    $userBasedCF1 = array();
    $itemBasedCF = array();
    $itemBasedCF1 = array();
    $contentBased = array();
    $contentBased1 = array();
    //get all products ids
    foreach ($allProduct as $key => $product) {
      $itemIDs[$product['id']] = $product['id'];
    }
    $finalPredictionArray = array();
    foreach ($AllPredictions as $UserID => $AllPrediction) {
      $userID = $AllPrediction['userID'];
      $useBasedpredictions = json_decode($AllPrediction['user_cf_prediction'],true);
      foreach ($useBasedpredictions as $key => $prediction) {
        $userBasedCF[$prediction['product_id']] = $prediction['predicted_rating'];
      }
      $itemBasedpredictions = json_decode($AllPrediction['item_cf_prediction'],true);
      foreach ($itemBasedpredictions as $key => $prediction) {
        $itemBasedCF[$prediction['product_id']] = $prediction['predicted_rating'];
      }
      $contentPredictions = json_decode($AllPrediction['cb_prediction'],true);
      foreach ($contentPredictions as $key => $prediction) {
        $contentBased[$prediction['product_id']] = $prediction['similarity'];
      }
      //compute weighted prediction for each unique user
      $finalPredictionArray[$userID] = self::computeWeightedPrediction($userID,$itemIDs,$userBasedCF,$itemBasedCF,$contentBased);;
    }
    $response = self::insertUpdateRecommendation($finalPredictionArray);
    debugfilewriter($finalPredictionArray);

    return $response;
  }

  //compute weighted prediction
  private static function computeWeightedPrediction($userID,$itemIDs,$userBasedCF,$itemBasedCF,$contentBased){
    $PredictionArray = array();
    $userBasedCF1[$userID] = $userBasedCF;
    $itemBasedCF1[$userID] = $itemBasedCF;
    $contentBased1[$userID] = $contentBased;
    foreach ($itemIDs as $key => $itemID) {
      $x =$userBasedCF1[$userID];
      $y =$itemBasedCF1[$userID];
      $z =$contentBased1[$userID];
      $xx=0; $yy=0; $zz=0;
      if(array_key_exists($itemID,$z)){
        $zz = $z[$itemID];
      }
      if(array_key_exists($itemID,$x)){
        $xx = $x[$itemID];
      }
      if(array_key_exists($itemID,$y)){
        $yy = $y[$itemID];
      }
      $final = ($zz * self::CONTENT_WEIGHT) + ($xx * self::USERBASED_CF_WEIGHT) + ($yy * self::ITEMBASED_CF_WEIGHT);
      if( $final != 0){
        $PredictionArray[$itemID] = $final;
      }
    }
    if(count($PredictionArray) != 0){
      arsort($PredictionArray);
      $PredictionArray = array_slice($PredictionArray, 0, 10, true); //upto 10 top weighted item
    }
    $predicted_rating = array();
    foreach ($PredictionArray as $key => $value) {
      $predicted_rating[] = array(
          'product_id'       => +$key,
          'weight' => toDecimal(+$value,3),
        );
    }
    $recommended= json_encode($predicted_rating);
    return $recommended;
  }
//insert/update database from built query
  private static function insertUpdateRecommendation($recommendations){
    $updated_time = date("Y-m-d h:i:s", time());
    $query = 'REPLACE INTO hybrid_recommendation VALUES';
    $query_parts = array();
    foreach ($recommendations as $userID => $prediction) {
      $query_parts[] = "('" . $userID . "', '" . $prediction . "', '" . $updated_time . "')";
    }
    $query .= implode(',', $query_parts);
    $PC = new PredictionController();
    //insert or update all predictions to database
    $result = $PC->insertReplacePrediction($query);
    //update recommender last run time
    $sql =  "UPDATE `recommender_last_run` SET `hybrid_last_updated` = ? WHERE `recommender_last_run`.`id` = 1";
    $PC->updateRecLastRun($sql);
    return $result;
  }
}
