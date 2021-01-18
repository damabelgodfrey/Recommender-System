<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/UserItemRatingMatrix.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ItemFeatureSimComputation.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/PredictionController.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/algorithms/PearsonCorrelation.php';
/**
* Compute item collaborative filtering based
* on item nearest neighbour
*@ return predicted item.
*/
class ItemBasedCFEngine{
  private static $sim;
  private static $ItemPeerGroups = array();
  private static $userItemRatingMatrix = array();
  private static $itemUserRatingMatrix = array();
  const SIMILARITY_ALG = "EuclideanDistance";
  const SIMILARITY_THESHOLD = 0;
  // compute Ite based Collaborative filtering
  public static function computeItemBasedCF($user_id, $userItemRatingMatrix,$matrix){
    $prediction = array();
    self::$userItemRatingMatrix = $userItemRatingMatrix;
    $ItemPeerGroups = array();
    $userMeanRating = array_sum($userItemRatingMatrix)/count($userItemRatingMatrix);
    foreach ($userItemRatingMatrix as $id => $rating):
      $ItemPeerGroup = self::computeItemSimilarity($id,$matrix);
      $ItemPeerGroups[$id.'?'.$rating] = array_slice($ItemPeerGroup, 0, 5, true); //pick top 5
    endforeach;
    self::$ItemPeerGroups = $ItemPeerGroups;
    $itemPredictionArray = self::computeWeightedRatingprediction($ItemPeerGroups,$userMeanRating);
    $prediction = self::addPredictionToNearestItemNearbors($user_id,$itemPredictionArray);
    arsort($prediction);
    $prediction = array_slice($prediction, 0, 10, true);
    foreach ($prediction as $key => $value) {
      $value = floatval($value);
      $key = +$key;
      $predicted_rating[] = array(
        'product_id'       => $key,
        'predicted_rating' => $value,
      );
    }
    $prediction = json_encode($predicted_rating);
    return $prediction;
  }
  //items as columns and users as rows
  private static function getTransposedUserItemMatrix($matrix){
    $itemUserRatingMatrix = self:: $itemUserRatingMatrix;
    if(count($itemUserRatingMatrix) !== 0){
      return $itemUserRatingMatrix;
    }else{
      $transposedMatrix = array();
      foreach($matrix as $User => $UserItemRating){
        foreach($UserItemRating as $item => $rating)
        {
          $transposedMatrix[$item][$User] = $rating;
        }
      }
      self::$itemUserRatingMatrix = $transposedMatrix;
      return $transposedMatrix;
    }
  }

  private static function computeItemSimilarity($currentItem,$matrix)
  {
    //transform user item Matrix to item user matrix to compute siilarity
    $transformedItemMatrix = self::getTransposedUserItemMatrix($matrix);
    $ItemSim = array();
    foreach($transformedItemMatrix as $otherItem=>$values):
      if($otherItem !== $currentItem):
        $simAlgorithm = self::SIMILARITY_ALG;
        switch ($simAlgorithm) {
          case 'CosineSimilarity':
          $similarity = RatingBasedCosineSimilarity::computeF_CosineSimilarity($transformedItemMatrix,$currentItem,$otherItem);
          break;

          case 'EuclideanDistance':
          $similarity = EuclideanDistance::computeEuclideanDistance2($transformedItemMatrix,$currentItem,$otherItem);
          break;

          case 'PearsonCorrelation':
          $similarity = PearsonCorrelation::getCorrelation($transformedItemMatrix,$currentItem,$otherItem);
          break;

          case 'AdjustedCosineSim':
          $similarity = CF_AdjustedCosineSimilarity::conputeF_adjustedCosineSimilarity($transformedItemMatrix,$currentItem,$otherItem);
          break;

          default:
          break;
        }
        if($similarity > self::SIMILARITY_THESHOLD):
          $ItemSim[$otherItem] = $similarity;
        endif;
      endif;
    endforeach;
    arsort($ItemSim);
    return $ItemSim;
  }
  //compute rating prediction for items in item peer group
  private static function computeWeightedRatingprediction($ItemPeerGroups,$userMeanRating){
    $sim_Rating_product =array();
    $simSummation =array();
    foreach ($ItemPeerGroups as $current_item_id => $ItemPeerGroup) {
      $id_rating = explode('?',$current_item_id);
      $user_rating = (int)$id_rating[1];
      $id = (int)$id_rating[0];
      foreach ($ItemPeerGroup as $other_item_id => $similarity):
        //prediction uses the weigted average of rating of the current user for the current items and the avarage rating of the current user .
        $finalWeightedRating = ($user_rating * 0.75) + ($userMeanRating * 0.25);
        if(!array_key_exists($id,$sim_Rating_product)){
          $sim_Rating_product[$id] = 0;
        }
        $sim_Rating_product[$id]+=$finalWeightedRating*$similarity;
        if(!array_key_exists($id, $simSummation)){
          $simSummation[$id] = 0;
        }
        $simSummation[$id]+=$similarity;
      endforeach;
    }

    $itemRatingPredictionArray = array();
    //compute summation of Sim*RU1/sum of simmilarity
    foreach ($sim_Rating_product as $key => $sum_Sim_rating_product) {
      if($simSummation[$key] != 0){
        $itemRatingPredictionArray[$key]= to2Decimal($sum_Sim_rating_product/$simSummation[$key]);
      }
    }
    //RootMeanSquareEstimation::computeRootMeanSqEst($itemRatingPredictionArray);
    arsort($itemRatingPredictionArray);
    return $itemRatingPredictionArray;
  }

  //pass peer predicted rating to all similar items in each item peer group.
  // filter out item already rated by user if it exist.
  private static function addPredictionToNearestItemNearbors($user_id,$itemRatingPredictionArray){
    $ItemPeerGroups =self::$ItemPeerGroups;
    $ItemPrediction = array();
    foreach ($itemRatingPredictionArray as $p_id => $predictedRating) {
      foreach ($ItemPeerGroups as $other_item_id => $ItemPeerGroup) {
        $_id_rating = explode('?',$other_item_id);
        $_id = $_id_rating[0];
        // add predicted rating in each iterations
        if($_id+0 == $p_id+0){
          foreach ($ItemPeerGroup as $item_id => $similarity) {
            if(!array_key_exists($item_id,$ItemPrediction)){ //if an item appear in multiple item group assign the higher rating
              $ItemPrediction[$item_id] = $predictedRating;
            }else{
              if($ItemPrediction[$item_id]+0 < $predictedRating+0){
                $ItemPrediction[$item_id] = $predictedRating;
              }
            }
          }
        }
      }
    }
    $finalItemBasedPrediction = array_slice($ItemPrediction, 0, 10, true); //upto 10 top rated item
    return $finalItemBasedPrediction;
  }
}
