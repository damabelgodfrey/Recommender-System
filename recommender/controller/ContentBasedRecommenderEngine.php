<?php
/**
 * compute content based recommendation from user profile
 * compute siilarity between user profile and item profile
 * @ return Top-N items and score from item profiles that is similar to current user profile
 * @return ranked products based on similarity to user profile.
 */
class ContentBasedRecommenderEngine
{
  const SIMILARITY_THRESHOLD = 0; // specify similarity threshold.
  const ALGORITHM = "ConsineSimilarity";
  private static $uProfileBrands;
  private static $uProfileCategories;
  // get user profile informations
  //@return weighted tokens
  private static function processUserProfile($user_id,$profile){
  $existingProfiling = array();

      $itemIDCatBrand = explode('::',$profile['itemID_cat_brand']);
      $itemCatArr = explode(',',$itemIDCatBrand[1]);
      $itemBrandArr = explode(',',$itemIDCatBrand[2]);
      $existingProfiling = json_decode($profile['profile'],true);
    $obj = new productController();
    $brands = $obj->getBrandSet($itemBrandArr);
    $categories = $obj->getCategorySet($itemCatArr);
    $brand="";
    $cat = "";
    foreach ($brands as $key => $value) {
     $brand .= preg_replace('/\s+/', '', $value['brand']).' ';
    }
    foreach ($categories as $key => $value) {
     $cat .= preg_replace('/\s+/', '', $value['category']).' ';
    }
    self::$uProfileCategories = trim($cat);
    self::$uProfileBrands = trim($brand);
    foreach ($existingProfiling as $key => $profile) {
        $weight = $profile['weight'];
        for ($i=0; $i < $weight; $i++) {
          $profileTokens[]= $profile['token'];
        }
    }
    $profileTokens = trim(implode(' ', $profileTokens));
    return $profileTokens;
}
 //uses several algorithms to compute item similarity using specified features
 //product descripted and product tag
 //ranks all processed items by similarity score against current user profile.
 public static function computeContentBasedPrediction($user_id,$userProfiles): string{
  $recommendedArray = array();
  $recommended ="";
  $userProfileTokens = self::processUserProfile($user_id,$userProfiles); // get current user profile
  $ucatBrand = self::$uProfileCategories.' '.self::$uProfileBrands;
  if($userProfileTokens != " ") {
   $stopWord = getStopwordsFromFile();
   $itempObj = new ItemProfiler();
   $itemProfiles = $itempObj->getItemProfile_Brand_Category(); //fetch item profiller info and get secondary info from other tables
   $user_email =   $_SESSION['user_email'];
   $transObj = new TransactionController();
   $purchaseItemIDArr = $transObj->getUserTransactions($user_email);
   foreach ($itemProfiles as $itemprofile) {
     $itemID =  $itemprofile['itemID'];
     $p2_aveRating =  $itemprofile['average_rating'];
     $itemProfileTokens = json_decode($itemprofile['profile'],true)['tag'];
     $iProfileBrand = preg_replace('/\s+/', '', $itemprofile['brand']);// reove spacing between multiple worded brand
     $iProfileCategory = preg_replace('/\s+/', '', $itemprofile['category']);
     $icatbrand = $iProfileBrand.' '.$iProfileCategory;
     $purchaseCheck = 0;
     if(!array_key_exists($itemID,$purchaseItemIDArr)){ //exclude products already bought by user
      $purchaseCheck = 1;
     }
     if($purchaseCheck ==1){

     switch (self::ALGORITHM) {
       case 'LevenshteinDistance':
         $result = D_LevenshteinDistance::getLevenshteinDistance($ucatBrand, $userProfileTokens, $icatbrand, $itemProfileTokens);
         break;
       case 'JaccardSimilarityCoefficient':
         $result = JaccardSimilarity::getJaccardSimilarityWeight($ucatBrand, $userProfileTokens, $icatbrand, $itemProfileTokens);
         break;
       case 'ConsineSimilarity':
         $r1 = TokenBasedCosineSimilarity::getCBConsineSimilarity($userProfileTokens,$itemProfileTokens);
         $r2 = TokenBasedCosineSimilarity::getCBConsineSimilarity($ucatBrand,$icatbrand);
         $result = ($r1*0.75) + ($r2*0.25); // brand and category is asign a lesser weight
         break;
       case 'CosineRatingSimilarityWeighted':
         $result = TokenBasedCosineSimilarity::getCBCosineRatingSimilarityWeighted($userProfileTokens, $itemProfileTokens, $p2_aveRating);
         break;
       default:
         break;
      }
     if($result != self::SIMILARITY_THRESHOLD){
       $recommendedArray[$itemID] = to2Decimal($result);
     }
   }
 }

    if(count($recommendedArray) != 0){
     arsort($recommendedArray);
     $recommendedArray = array_slice($recommendedArray, 0, 10, true);
     foreach ($recommendedArray as $key => $value) {
       $value = floatval($value);
       $key = +$key;
       $predicted_rating[] = array(
           'product_id'       => $key,
           'similarity' => $value,
         );
     }
     $recommended= json_encode($predicted_rating);
    }
  }
   return $recommended;
 }
}
