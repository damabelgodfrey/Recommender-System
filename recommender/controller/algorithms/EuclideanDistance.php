<?php
/**
 * compute EuclideanDistance
 */
class EuclideanDistance
{
  //conpute similarity distance only for similar user or items
  public static function computeEuclideanDistance($UserRatingMatrix, $baseToken,$otherToken){
    $similar = array();
    $sum = 0;
    if(isset($UserRatingMatrix[$baseToken])){
      //can finter out number of co-rated item is considered to use user as nearest neighbor
      foreach ($UserRatingMatrix[$baseToken] as $key => $value) { //if co-rated item exist
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
}
