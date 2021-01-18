<?php
/**
 * Computes Pearson correlation betwwen two user based on rating
 * @return pearson correlation coefficient
 */
class PearsonCorrelation
{
  public static function getCorrelation($matrix,$user1,$Users2){
    $user1metrix =array();
    $user2metrix = array();
    if(isset($matrix[$user1])){
      foreach ($matrix[$user1] as $key => $value) { //check if user has rated a product also rated by user2
        if(array_key_exists($key,$matrix[$Users2])){
            $user1metrix[$key] = $value;
            $user2metrix[$key] = $matrix[$Users2][$key];
        }
      }
      if(count($user1metrix) ==0){
        return 0;
      }

     $user1MeanRating = array_sum($user1metrix)/count($user1metrix);
     $user2MeanRating = array_sum($user2metrix)/count($user2metrix);
     $diffprod=0;$xdiff2=0;$ydiff2=0;
      foreach ($matrix[$user1] as $key => $user1Rating) {
        if(array_key_exists($key,$matrix[$Users2])){
             $xdiff=$user1Rating-$user1MeanRating;
             $ydiff=$matrix[$Users2][$key]-$user2MeanRating;
             $diffprod += $xdiff*$ydiff;
             $xdiff2+=pow($xdiff,2);
             $ydiff2+=pow($ydiff,2);
        }
      }
      $b = sqrt($xdiff2*$ydiff2);
      if($b == 0){
        return 0;
      }
      return $diffprod/$b;
    }else{
      return false;
    }
  }
}
