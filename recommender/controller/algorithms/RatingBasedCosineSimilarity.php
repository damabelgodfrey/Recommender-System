<?php
/**
* Computes cosine similarity between two users or item based on Ratings
* current variable can be current user or item.
* target variable can be target user or item.
*@return cosine similarity coeffient
 */
class RatingBasedCosineSimilarity
{
//compute cosine similarity using user with mutual ratings
public static function computeF_CosineSimilarity($matrix,$current,$target): float{
  $similarRatingGrid = array();
  $sumXY = 0;
  $sumYY = 0;
  $sumXX = 0;
  if(isset($matrix[$current])){
    foreach ($matrix[$current] as $key => $currentRating) { //check if user have rated a product also rated by other user
      if(array_key_exists($key,$matrix[$target])){
          $similarRatingGrid[$key] = $currentRating;
      }
    }
    if(count($similarRatingGrid) ==0){
      return 0;
    }else {
      foreach ($matrix[$current] as $key => $currentRating){
        if(array_key_exists($key,$matrix[$target])){
             $xy = $matrix[$current][$key] * $matrix[$target][$key];
             $sumXY += $xy;
             $sumXX += pow($matrix[$current][$key],2);
             $sumYY += pow($matrix[$target][$key],2);
        }
      }
   return $sumXY /sqrt($sumXX * $sumYY);
   }
  }else{
    return false;
  }
}
//compute cosine similarity using all user ratings
public static function computeF_CosineSimilarity2($matrix,$current,$target){
  $sumXY = 0;
  $sumYY = 0;
  $sumXX = 0;
  if(isset($matrix[$current])){
      foreach ($matrix[$target] as $key => $targetRating){
        $sumYY += pow($matrix[$target][$key],2);
      }
      foreach ($matrix[$current] as $key => $currentRating){
        $sumXX += pow($matrix[$current][$key],2);
        if(array_key_exists($key,$matrix[$target])){
             $xy = $matrix[$current][$key] * $matrix[$target][$key];
             $sumXY += $xy;
        }
      }
      if($sumXX === 0 || $sumYY ===0){
        return 0;
      }else{
        return $sumXY /sqrt($sumXX * $sumYY);
      }
  }else{
    return false;
  }
 }
}
