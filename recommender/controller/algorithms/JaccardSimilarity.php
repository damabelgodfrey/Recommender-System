<?php
/**
 * Compute Jaccard Similarity coefficient for imputed product features
 * @ return Similarity between two strings
 */
class JaccardSimilarity{
  const TAGWEIGHT = 0.75;
  const OTHERPROWEIGHT = 0.25;
  // compute similarity with all property join
  public static function getJaccardSimilarity($token1, $token1tag, $token2, $token2tag) : float{
    $currentToken = $token1.''.$token1tag;
    $otherToken = $token2.''.$token2tag;
    $JC = self::computeJaccardSimilarity($currentToken, $otherToken);
	  return $JC;
 }
  // compute similarity with weight
  public static function getJaccardSimilarityWeight($token1, $token1tag, $token2, $token2tag) : float{
    $p1 = self:: computeJaccardSimilarity($token1, $token2);
    $p2 = self:: computeJaccardSimilarity($token1tag, $token2tag);
    return ($p1 * self::OTHERPROWEIGHT) + ($p2 * self::TAGWEIGHT);
  }

 private static function computeJaccardSimilarity($token1, $token2) : float{
  $currentToken = $token1;
  $otherToken = $token2;
  $currentTokensArr = preg_split('/[\s,]+/', $currentToken);
  $otherTokensArr = preg_split('/[\s,]+/', $otherToken);
  $intersection = array_intersect($otherTokensArr, $currentTokensArr ); //intersection
  $union = array_unique(array_merge($currentTokensArr, $otherTokensArr )); //union
  $noOfIntersection = count($intersection);
  $noOfUnion = count($union);
  $jaccard_sim_coefficient = $noOfIntersection / $noOfUnion; //
  return $jaccard_sim_coefficient;
 }
}
