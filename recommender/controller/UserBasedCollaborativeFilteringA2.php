<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/algorithms/EuclideanDistance.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/algorithms/RatingBasedCosineSimilarity.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/algorithms/CF_AdjustedCosineSimilarity.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/algorithms/PearsonCorrelation.php';
/**
 * User based collaborative filtering algorithm 2
 */
class UserBasedCollaborativeFilteringA2
{

  private static $similarity;
  private static $sim_Rating_product = array();
  private static $simSummation = array();
  private static $userMeanRating;

  public static function getPredict($simAlgorithm,$allUserMatrix, $currentUser){
  self:: $sim_Rating_product = array();
  self::$simSummation =array();
  $UserSimilarityArr = array();
  if(isset($allUserMatrix[$currentUser])){
    foreach($allUserMatrix as $otherUser =>$value){
      if($otherUser !=$currentUser){
        switch ($simAlgorithm) {
          case 'CosineSimilarity':
            self::$similarity = RatingBasedCosineSimilarity::computeF_CosineSimilarity($allUserMatrix,$currentUser,$otherUser);
            break;

          case 'EuclideanDistance':
            self::$similarity = EuclideanDistance::computeEuclideanDistance($allUserMatrix,$currentUser,$otherUser);
            break;

          case 'PearsonCorrelation':
            self::$similarity = PearsonCorrelation::my_pearson_correlation2($allUserMatrix,$currentUser,$otherUser);
            break;

          case 'AdjustedCosineSim':
            self::$similarity = CF_AdjustedCosineSimilarity::conputeF_adjustedCosineSimilarity($allUserMatrix,$currentUser,$otherUser);
            break;

          default:
            break;
        }
      self::prediction($allUserMatrix,$currentUser,$otherUser);
      debugfilewriter("\nUser Similarity Coefficient A2".' '.$otherUser.' '.self::$similarity);
      $UserSimilarityArr[$otherUser]= self::$similarity;
      }
    }
  }
    $A2 =self::computeRatingPrediction();
    return $A2;
  }
  public static function computeRatingPrediction(){
    $itemRatingPredictionArray = array();
    $sim_Rating_product =self::$sim_Rating_product;
    $simSummation= self::$simSummation;
    $userMeanR = self::$userMeanRating;
    foreach ($sim_Rating_product as $key => $value) {
      if($simSummation[$key] != 0){
        $itemRatingPredictionArray[$key]= to2Decimal($userMeanR +($value/$simSummation[$key]));
      }

    }

    arsort($itemRatingPredictionArray);
    debugfilewriter("\nRoot Mean Sqaure Estimation A2\n");
    RootMeanSquareEstimation::computeRootMeanSqEst($itemRatingPredictionArray);
    return $itemRatingPredictionArray;
  }

  public static function prediction($ExistingMatrix,$currentUser,$otherUser){
  $sim = self::$similarity;
    $otherUserCounter=0;
    $usertotalRating = 0;
    $userCounter = 0;
    $userTotalRating = 0;
    if(isset($ExistingMatrix[$currentUser])){ //check if current user has a rating in user metrix
      foreach($ExistingMatrix[$otherUser] as $key=>$value){
        $usertotalRating +=$value; //total of other user all ratings
        $otherUserCounter++;
       }
      foreach($ExistingMatrix[$currentUser] as $key=>$value){
        $userTotalRating +=$value;
        $userCounter++;
      }
        $otherUserRatingMean = $usertotalRating/$otherUserCounter; // user mean rating
        self::$userMeanRating = $userTotalRating/$userCounter;
      foreach($ExistingMatrix[$otherUser] as $key=>$value){
        if(!array_key_exists($key,$ExistingMatrix[$currentUser])){
          if(!array_key_exists($key,self::$sim_Rating_product)){
            self::$sim_Rating_product[$key] = 0;
          }
          $x = ($ExistingMatrix[$otherUser][$key]-$otherUserRatingMean)*$sim;
          self::$sim_Rating_product[$key]+=$x;
          if(!array_key_exists($key,self::$simSummation)){
            self::$simSummation[$key] = 0;
          }
          self::$simSummation[$key]+=$sim;
        }
      }
    }
  }
}
