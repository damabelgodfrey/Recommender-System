<?php
class CF_AdjustedCosineSimilarity{

  public static function conputeF_adjustedCosineSimilarity($matrix,$user1,$Users2){
      $user1metrix =array();
      $user2metrix = array();
      if(isset($matrix[$user1])){
        foreach ($matrix[$user1] as $key => $value) { //check if user has rated a product also rated by user2
          if(array_key_exists($key,$matrix[$Users2])){
              $user1metrix[$key] = $value;
              $user2metrix[$key] = $matrix[$Users2][$key];
          }
        }
        if(count($user1metrix) == 0){
          return 0;
        }else{
        $user1MeanRating = array_sum($user1metrix)/count($user1metrix);
        $user2MeanRating = array_sum($user2metrix)/count($user2metrix);
        $item1=0; $item2=0;
         foreach ($matrix[$user1] as $key => $user1Rating) {
           if(array_key_exists($key,$matrix[$Users2])){
                $item1+=$user1Rating-$user1MeanRating;
                $item2+=$matrix[$Users2][$key]-$user1MeanRating;
                //$item2+=$matrix[$Users2][$key]-$user2MeanRating;
           }
         }
         if($item1 ==0 || $item2 ==0){
           return 0;
         }
        $result = ($item1*$item2)/(sqrt($item1*$item1)*sqrt($item2*$item2));
        return $result;
      }
    }else{
    return false;
  }
  }

  public static function conputeItemF_adjustedCosineSimilarity($matrix,$currentItem,$otherItem){
      $currentItem_metrix =array();
      $otherItem_metrix = array();
      if(isset($matrix[$currentItem])){
        foreach ($matrix[$currentItem] as $key => $value) { //check if user has rated a product also rated by user2
          if(array_key_exists($key,$matrix[$otherItem])){
              $currentItem_metrix[$key] = $value;
              $otherItem_metrix[$key] = $matrix[$otherItem][$key];
          }
        }
        if(count($currentItem_metrix) == 0){
          return 0;
        }else{
        $currentItem_MeanRating = array_sum($currentItem_metrix)/count($currentItem_metrix);
        $otherItem_MeanRating = array_sum($otherItem_metrix)/count($otherItem_metrix);
        $item1=0; $item2=0;
         foreach ($matrix[$currentItem] as $key => $currentItemRating) {
           if(array_key_exists($key,$matrix[$otherItem])){
                $item1+=$currentItemRating-$currentItem_MeanRating;
                $item2+=$matrix[$otherItem][$key]-$otherItem_MeanRating;
           }
         }
         if($item1 ==0 || $item2 ==0){
           return 0;
         }
        $result = ($item1*$item2)/(sqrt($item1*$item1)*sqrt($item2*$item2));
        return $result;
      }
    }else{
    return false;
  }
  }
}
