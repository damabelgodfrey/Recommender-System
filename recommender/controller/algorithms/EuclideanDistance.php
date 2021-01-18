<?php
/**
 *
 */
class EuclideanDistance
{
  //conpute similarity distance only for similar user
  public static function computeEuclideanDistance($UserRatingMatrix, $baseToken,$otherToken){
    $similar = array();
    $sum = 0;
    if(isset($UserRatingMatrix[$baseToken])){
      foreach ($UserRatingMatrix[$baseToken] as $key => $value) { //check if user has purchase a product also purchased by user2
        if(array_key_exists($key,$UserRatingMatrix[$otherToken])){
            $similar[$key] = $value;
        }
      }
      if(count($similar) ==0){
        return 0;
      }
      foreach ($UserRatingMatrix[$baseToken] as $key => $value) {
        if(array_key_exists($key,$UserRatingMatrix[$otherToken])){
             $sum += pow($value - $UserRatingMatrix[$otherToken][$key],2);
        }
      }
     return 1/(1+sqrt($sum));
    }else{
      return false;
    }
  }

  public static function computeEuclideanDistance2($UserRatingMatrix, $baseToken,$otherToken){
    $similarRatingGrid = array();
    $sumXY = 0;
    if(isset($UserRatingMatrix[$baseToken])){
        foreach ($UserRatingMatrix[$baseToken] as $key => $baseTokenRating) {
          if(array_key_exists($key,$UserRatingMatrix[$otherToken])){
               $xy = $baseTokenRating -  $UserRatingMatrix[$otherToken][$key];
               $sumXY += pow($xy,2);
          }
        }

     return 1/(1+sqrt($sumXY));
    }else{
      return false;
    }
 }
}
