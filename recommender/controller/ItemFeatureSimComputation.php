<?php
/**
 * Compute the similarity between all items against current item
 * Uses specified similarity algorithms
 * @ return an array of having similar items id as key and similarity score as value.
 */
class ItemFeatureSimComputation
{
   private static $similarity_Threshold = 0; // specify similarity threshold.
  //uses several algorithms to compute item similarity using specified features
  //Product title, product descripted and product tag
  //ranks all items by similarity score against current item id (p_id).
  public static function getFeatureSimCoefficient($class, $simAlgorithm,$CFArray,$P_id,$rating=0): array{
    $product = new ProductController();
    $recommendedArray = array();
    if(count($CFArray)!=0 && $class == "CollaborativeFilteringInit"){
      $OtherProductQuery = $product->requestGroupProduct($CFArray);
    }else{
      $OtherProductQuery = $product->getAllProducts();
    }
    $productQuery = $product->getProduct($P_id);
    $stopWord = getStopwordsFromFile();
    foreach ($productQuery as $product) {
      $noOfSynonysPerword= 2;
      $p1_aveRating = +$product['product_average_rating'];
      $tags =DictionaryLookUp::requestAllSynonyms($product['p_keyword'],$noOfSynonysPerword);
      $thisItemTags= processContent($stopWord, $tags);
      $thisItemFeatures= processContent($stopWord, $product['title'].' '.$product['description']);
    }

    if(is_logged_in()){
      $user_email =   $_SESSION['user_email'];
      $transObj = new TransactionController();
      $idArray = $transObj->getUserTransactions($user_email);
      }
      foreach ($OtherProductQuery as $otherProduct) {
         $otherProductID=  $otherProduct['id'];
         $condition = 0;
        if(is_logged_in()){
          if($otherProductID != $P_id && !array_key_exists($otherProductID,$idArray)){ //exclude products already bought by user
           if($class =="ItemBasedCollaborativeFiltering"){ // exclude item rated by user. Only find item similar to rated item
             if(!array_key_exists($otherProductID,$CFArray)){
               $condition = 1;
             }
           }else{
             $condition = 1;
           }
          }
        }else{
          if($otherProductID != $P_id){
            $condition = 1;
          }
        }
        if($condition ==1){
         //$otherItemFeatures = processContent($stopWord, $otherProduct['title'].' '.$otherProduct['description'].' '.$otherProduct['p_keyword']);
         //for weighted product property input
         $otherItemFeatures= processContent($stopWord, $otherProduct['title'].' '.$otherProduct['description']);
         $otherItemTags= processContent($stopWord, $otherProduct['p_keyword']);
         $p2_aveRating = (double)$otherProduct['product_average_rating'];
        switch ($simAlgorithm) {
          case 'LevenshteinDistance':
            $result = D_LevenshteinDistance::getLevenshteinDistance($thisItemFeatures, $thisItemTags, $otherItemFeatures, $otherItemTags);
            break;
          case 'JaccardSimilarityCoefficient':
            //$result = JaccardSimilarity::getJaccardSimilarity($thisItemFeatures, $thisItemTags, $otherItemFeatures, $otherItemTags);
            $result2 = JaccardSimilarity::getJaccardSimilarityWeight($thisItemFeatures, $thisItemTags, $otherItemFeatures, $otherItemTags);
            break;
          case 'ConsineSimilarity':
            $result = TokenBasedCosineSimilarity::getCBConsineSimilarity($thisItemFeatures.' '.$thisItemTags, $otherItemFeatures.' ' .$otherItemTags);
            break;
            case 'TokenBasedAdjustedCosineSimilarity':
              $userAveRating = $rating;
              $result = TokenBasedAdjCosineSimilarity::getTB_AdjustedCBConsineSim($thisItemFeatures.' '.$thisItemTags, $otherItemFeatures.' ' .$otherItemTags,$userAveRating);
            //  $result = TokenBasedAdjCosineSimilarity::transformPreferences(11);
              break;
          case 'CosineRatingSimilarityWeighted':
            $result = TokenBasedCosineSimilarity::getCBCosineRatingSimilarityWeighted($thisItemFeatures, $otherItemFeatures, $p2_aveRating);
            break;
          case 'CosineSimilarityRatingTagWeighted':
            $result = TokenBasedCosineSimilarity::getCBCosineSimilarityRatingTagWeighted($thisItemFeatures, $thisItemTags,
                                                                             $otherItemFeatures, $otherItemTags,$p2_aveRating);
            break;
          default:
            break;
         }
        if($result != self::$similarity_Threshold){
          $recommendedArray[$otherProduct['id']] = to2Decimal($result);
        }
        }
    }
    if(count($recommendedArray) != 0){
      asort($recommendedArray);
      $recommendedArray = array_slice($recommendedArray, 0, 5, true);
      debugfilewriter("\n$class.' => '.Token Based Similarity\n");
      debugfilewriter($recommendedArray);
    }
    return $recommendedArray;
  }
}
