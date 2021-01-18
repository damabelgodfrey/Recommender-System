<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/UserItemRatingMatrix.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ItemFeatureSimComputation.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/PredictionController.php';
/**
 *
 */
class ItemBasedCollaborativeFiltering
{
  private static $sim;
  private static $ItemPeerGroups = array();
  private static $userItemRatingMatrix = array();
  private static $sim_Rating_product =array();
  private static $simSummation =array();

  // get all user rating and compute an item rating matrix for user
  private static function getUserMatrix($user_id){
  $type ="User";
  $userItemRatingMatrix = array();
  $userMatrix =UserItemRatingMatrix::createRatingMatrix($type,$user_id);
  foreach ($userMatrix as $key => $user) {
    foreach ($user as $user_id => $rating) {
      $userItemRatingMatrix[$user_id]= $rating;
    }
  }
   self::$userItemRatingMatrix = $userItemRatingMatrix;
  return $userItemRatingMatrix;
  }

  //get item id
  //compute similarity of each item to the item id rated by user
  // keep top 5 item.
  //if similar item exist in multiple item group remove the item with lower score and retain higher score item.
  //foreach of the top 5 item compute the prediction using the item id rating, average rating of item
  // and the sim score and return an array with items
  // id and sim score

  // compute Ite based Collaborative filtering
 public static function computeItemBasedCF($user_id){
   $userItemRatingMatrix = self::getUserMatrix($user_id);
   //LevenshteinDistance,JaccardSimilarityCoefficient, ConsineSimilarity,CosineRatingSimilarityWeighted,CosineSimilarityRatingTagWeighted
   if(count($userItemRatingMatrix) > 0){
     $userAveRating = array_sum($userItemRatingMatrix)/count($userItemRatingMatrix);
   }else{
     $userAveRating = 0;
   }

   $simAlgorithm ="TokenBasedAdjustedCosineSimilarity";
  foreach ($userItemRatingMatrix as $id => $rating):
    $ItemPeerGroup = ItemFeatureSimComputation::getFeatureSimCoefficient("ItemBasedCollaborativeFiltering",$simAlgorithm,$userItemRatingMatrix,$id,$userAveRating);
    $ItemPeerGroup = array_slice($ItemPeerGroup, 0, 5, true); //pick top 5
      $ItemPeerGroups[$id.'?'.$rating]=  $ItemPeerGroup;
      //compute prediction sim*rating/$sim
      self::prediction($ItemPeerGroups);
  endforeach;
  self::$ItemPeerGroups = $ItemPeerGroups;
  $A3 = self::computeRatingPrediction($user_id);
  return $A3;
  }
  //compute rating prediction for items in item peer group
  private static function prediction($ItemPeerGroups){
   foreach ($ItemPeerGroups as $current_item_id => $ItemPeerGroup) {
    $id_rating = explode('?',$current_item_id);
    $user_rating = (int)$id_rating[1];
    $id = (int)$id_rating[0];
    $obj = new ProductController();
    foreach ($ItemPeerGroup as $other_item_id => $similarity):
      //prediction uses the weigted average of rating of the user for set of similar items and the avarage rating of individual item .
      $itemAverageRating = $obj->getProduct($other_item_id);
      if(count($itemAverageRating) == 1){ // check if item has an avarage rating
        if($itemAverageRating[0]['product_average_rating'] != " "){
          $itemAveRating =(double)$itemAverageRating[0]['product_average_rating'];
          $finalWeightedRating = ($user_rating * 0.75) + ($itemAveRating * 0.25);
        }else{
          $finalWeightedRating = $user_rating;
        }
      }else{
        $finalWeightedRating = $user_rating;
      }
        if(!array_key_exists($id,self::$sim_Rating_product)){
          self::$sim_Rating_product[$id] = 0;
        }
        self::$sim_Rating_product[$id]+=$finalWeightedRating*$similarity;
        if(!array_key_exists($id,self::$simSummation)){
          self::$simSummation[$id] = 0;
        }
        self::$simSummation[$id]+=$similarity;
      endforeach;
    }
  }
//compute summation of Sim*RU1/sum of simmilarity
  private static function computeRatingPrediction($user_id){
    $itemRatingPredictionArray = array();
    $sim_Rating_product =self::$sim_Rating_product;
    $simSummation= self::$simSummation;
    foreach ($sim_Rating_product as $key => $value) {
      if($simSummation[$key] != 0){
        $itemRatingPredictionArray[$key]= to2Decimal($value/$simSummation[$key]);
      }
    }
    //RootMeanSquareEstimation::computeRootMeanSqEst($itemRatingPredictionArray);
    //pass peer predicted rating to all items in each item peer group.
    arsort($itemRatingPredictionArray);
    $ItemPrediction = array();
    $ItemPeerGroups =self::$ItemPeerGroups;
    foreach ($itemRatingPredictionArray as $p_id => $predictedRating) {
      foreach ($ItemPeerGroups as $other_item_id => $ItemPeerGroup) {
       $_id_rating = explode('?',$other_item_id);
       $_id = $_id_rating[0];
       if($_id+0 == $p_id+0){
          foreach ($ItemPeerGroup as $id => $similarity) {
            if(!array_key_exists($id,$ItemPrediction)){ //if an item appear in multiple item group assign the higher rating
             $ItemPrediction[$id] = $predictedRating;
           }else{
             if($ItemPrediction[$id]+0 < $predictedRating+0){
                $ItemPrediction[$id] = $predictedRating;
              }
            }
          }
        }
      }
    }
    $UserRatingMatrix =self::$userItemRatingMatrix;
    $finalItemBasedPrediction = array_slice($ItemPrediction, 0, 10, true); //upto 10 top rated item

    debugfilewriter("Item Item Collaborative Filtering Results\n");
    debugfilewriter("User Rating Matrix\n");
    debugfilewriter($UserRatingMatrix);
    debugfilewriter("Item Peer Groups\n");
    debugfilewriter($ItemPeerGroups);
    debugfilewriter("Final item prediction Rating");
    debugfilewriter($finalItemBasedPrediction);

    //write to database
    $predictObj = new PredictionController();
    $predictObj ->insertCFComputation("itemBasedCF", $user_id, $finalItemBasedPrediction);
  return $finalItemBasedPrediction;
  }
}
