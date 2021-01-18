<?php
class CF_AdjustedCosineSimilarity{

  public static function computeF_adjustedCosineSimilarity($matrix,$currentItem,$otherItem){
     $currentItem_metrix =array();
     $result = 0;
     $otherItem_metrix = array();
     $userMatrix = array();
      if(array_key_exists($currentItem, $matrix)){
         $userMatrix = $matrix[$currentItem];
        foreach ($userMatrix as $key => $value) { //check if user has rated a product also rated by user2
              $currentItem_metrix[$key] = $value;
          }
          foreach ($matrix[$otherItem] as $key => $value) { //check if user has rated a product also rated by user2
                $otherItem_metrix[$key] = $value;
            }
        $currentItem_MeanRating = array_sum($currentItem_metrix)/count($currentItem_metrix);
        $otherItem_MeanRating = array_sum($otherItem_metrix)/count($otherItem_metrix);
        $item1=0; $item2=0;
        $b = 0; $c=0;
         foreach ($userMatrix as $key => $currentItemRating) {
           if(array_key_exists($key,$matrix[$otherItem])){
                $item1+=$currentItemRating-$currentItem_MeanRating;
                $item2+=$matrix[$otherItem][$key]-$otherItem_MeanRating;
                $b+= pow($currentItemRating-$currentItem_MeanRating,2);
                $c+= pow($matrix[$otherItem][$key]-$otherItem_MeanRating,2);

           }
         }
         if($item1 == 0 || $item2 == 0){
           $result = 0;
         }else{
           $result = ($item1*$item2)/( sqrt($b) * sqrt($c) );

         }
    }
    $b= 1/(1+sqrt($result));
    var_dump('result'.$result.'final'.$b);
    return $result;

  }
  }
