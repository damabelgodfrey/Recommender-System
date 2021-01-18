<?php
/**
 * compute the levenshtein distance or damerau levenshtein between two input texts.
 * @ return a levenshtein or Damerau levenshtein distance
 */
class D_LevenshteinDistance
{
  const ALGORITHMTYPE = "damerau_levenshtein";
  public static function getLevenshteinDistance($token1, $token1tag, $token2, $token2tag) : float{
    $token1 = $token1.''.$token1tag; //join all properties
    $token2 = $token2.''.$token2tag;
    $token1Arr  = str_split($token1);
    $token2Arr  = str_split($token2);
    $token1StrLen = count($token1Arr);
    $token2StrLen = count($token2Arr);
    //source token can be transformed entirely to an empty string
    //by droping all its characters
    for($i = 0; $i <= $token1StrLen; $i++){
      $distance[$i][0] = $i;
    }
    // targeting token2 can be acquired from the empty source token1
    //through inserting every characater
    for($j = 0; $j <= $token2StrLen; $j++){
      $distance[0][$j] = $j;
    }
    for($i = 1; $i <= $token1StrLen; $i++){
      for($j = 1; $j <= $token2StrLen; $j++){
        if($token1[$i-1] == $token2[$j-1]){
          $substitutionCost = 0;
        }else{
            $substitutionCost = 1;
        }
       $deletion = $distance[$i - 1][$j] + 1; //deletion
       $insertion = $distance[$i][$j - 1] + 1; //insertion
       $substitution = $distance[$i - 1][$j - 1] + $substitutionCost; //substitution
       $distance[$i][$j] = min($deletion,$insertion,$substitution);
       if(self::ALGORITHMTYPE == "damerau_levenshtein"){
         if($i > 1 && $j> 1 && $token1[$i-1] == $token2[$j-2] && $token1[$i-2] == $token2[$j-1]){
           $distance[$i][$j] == min($distance[$i][$j], $distance[$i-2][$j-2] + 1 ); //transposition
         }
       }
      }
    }
    $y1 = $distance[$token1StrLen][$token2StrLen];
    //$y2 = 1/(1+sqrt($y1)); // normaliseed value to [0..1]
    return $y1;
  }
}
