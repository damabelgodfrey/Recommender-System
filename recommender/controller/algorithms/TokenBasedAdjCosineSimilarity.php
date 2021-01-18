<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/algorithms/CF_AdjustedCosineSimilarity.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/UserItemRatingMatrix.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ProductController.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/UserController.php';

class TokenBasedAdjCosineSimilarity
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
    public static function getTB_AdjustedCBConsineSim($item1Info, $item2Info,$userAverating)
    {
       $item1Info = "agree love love";
       $item2Info = "agree love love"; // "2.4698484809835 //2.4149379628279 3.0204858345021 // 0.37016833484598
      //$item2Info = "beter love hhr"; //"3.7807297995115
      // $item2Info = " beter shower come you"; // 4.2893143353994
      //$item2Info = "the gate stren dhhe jjdhfg hhdu hhshf ggsg ncfbb gggshx bbxvd"; // "0.2832221398019



       self::tokenise($item1Info, $item2Info);
       $xToken = self::$item1Tokens;
       $yToken = self::$item2Tokens;

        $a = $b = $c = 0;
        $uniqueMergedTokens = array_merge($xToken,$yToken);
        $item1Product= array();
        $item2Product= array();
      	$xArray=array();
      	$yArray=array();
      	$key=0;
      	foreach ($uniqueMergedTokens as $token=>$val) {
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
      //	$item1RootSumProduct=array_sum($item1Product)/count($item2Product));
      	//$item2RootSumProduct=array_sum($item2Product)/count($item2Product));
      	for($k=0;$k<$key;$k++){
      	//	$xArray[$k]/=$item1RootSumProduct;
      	//	$yArray[$k]/=$item2RootSumProduct;

            // $a+=($xArray[$k] - $userAverating) * ($yArray[$k]- $userAverating);
          	// $b+= pow($xArray[$k]-$userAverating,2);
        		// $c+=pow($yArray[$k]-$userAverating,2) ;
            $a+=($xArray[$k] - $userAverating) * ($yArray[$k]- $userAverating);
            $b+= pow($xArray[$k]-$userAverating,2);
            $c+=pow($yArray[$k]-$userAverating,2) ;
          }


        if($b == 0){
          return 0;
        }

      	$result =  $a / (sqrt($b) +sqrt($c));
        return $result;
    }
}
