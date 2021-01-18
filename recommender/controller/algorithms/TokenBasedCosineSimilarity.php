<?php

class TokenBasedCosineSimilarity
{
    /**
    *
    *
    */
    private static $item1Tokens = array();
    private static $item2Tokens = array();
    const CONTENT_WEIGHT = 0.75;
    const RATING_WEIGHT = 0.25;
    const CONTENT_WEIGHT2 = 0.25; // content weight is less when computed with tag
    const TAG_WEIGHT = 0.75;

    private static function tokenise($item1, $item2){
      $array = preg_split('/[^[:alnum:]]+/', strtolower($item1));
      foreach($array as $item)
        {
        if(strlen($item)>2)
          @$tokensA[$item]++;
        }
      $array = preg_split('/[^[:alnum:]]+/', strtolower($item2));
      foreach($array as $item){
        if(strlen($item)>2)
          @$tokensB[$item]++;
      }
   self:: $item1Tokens = @$tokensA;
   self:: $item2Tokens = @$tokensB;
    }
    public static function getCBConsineSimilarity(&$item1Info, &$item2Info)
    {
    //$item1Info1 =  "peter love love paul";
    //$item2Info2 = "james love peter";
       self::tokenise($item1Info, $item2Info);
       $xToken = self::$item1Tokens;
       $yToken = self::$item2Tokens;
        $a = $b = $c = 0;
        $uniqueMergedTokens = array_merge($yToken,$xToken);
        $item1Product= array();
        $item2Product= array();
      	$xArray=array();
      	$yArray=array();
      	$key=0;
      	foreach ($uniqueMergedTokens as $token=>$val){
          if(array_key_exists($token,$xToken)){
           $xArray[$key]=  $val;
          }else{
           $xArray[$key] = 0;
          }
          if(array_key_exists($token,$yToken)){
          $yArray[$key]=  $val;
          }else{
            $yArray[$key] = 0;
          }
          $item1Product[] = pow($xArray[$key],2);
          $item2Product[] = pow($yArray[$key],2);
      		$key++;
      	}
      	$item1RootSumProduct=sqrt(array_sum($item1Product));
      	$item2RootSumProduct=sqrt(array_sum($item2Product));
      	for($k=0;$k<$key;$k++){
      		$xArray[$k]/=$item1RootSumProduct;
      		$yArray[$k]/=$item2RootSumProduct;

          $a+=$xArray[$k]*$yArray[$k];
        	$b+=$xArray[$k]*$xArray[$k];
      		$c+=$yArray[$k]*$yArray[$k];
      	}
        if($b == 0){
          return 0;
        }
        $cs = $a / sqrt($b * $c);
    //    var_dump($cs);
      	//return $a / sqrt($b * $c);
        return $cs;
    }

    public static function getCBCosineRatingSimilarityWeighted(&$item1Info, &$item2Info,&$p_aveRating){
      $similarity = self:: getCBConsineSimilarity($item1Info, $item2Info);
      $contentWeight = self:: CONTENT_WEIGHT;
      $ratingWeight = self::RATING_WEIGHT;
      if($similarity == 0 || $p_aveRating == 0){
        return $similarity;
      }
      //normalise rating 0-5 to range 0-1
        $nRating= ($p_aveRating ) / (5);
        $adjustedRatingSimilarity = ($similarity*$contentWeight) + ($nRating* $ratingWeight);
       return $adjustedRatingSimilarity;

    }

      public static function getCBCosineSimilarityRatingTagWeighted(&$thisItemProperties, &$thisItemTags, &$otherItemProperties, &$otherItemTags, &$p_aveRating){
      $tagWeight = self::TAG_WEIGHT;
      $contentWeight = self:: CONTENT_WEIGHT2;
      $otheritemPropSim = self::getCBCosineRatingSimilarityWeighted($thisItemProperties, $otherItemProperties,$p_aveRating); //get product content similarity for other features
      $itemtagsSim =self::getCBCosineRatingSimilarityWeighted($thisItemTags, $otherItemTags,$p_aveRating); //get content similarity for tag similarity between comparing content
      //if $otheritemPropSim and $itemtagsSim is zero instead of returning zero it would return a lesser result
      $adjustedRatingTagSimilarity = ($otheritemPropSim*$contentWeight) + ($itemtagsSim* $tagWeight);
      // normalise result to rating scale
        return $adjustedRatingTagSimilarity;
      }
}
