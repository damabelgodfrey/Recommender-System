<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DBh.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ProductController.php';
/**
* This class build user profile based on user activities on the system
* weighted tokens is based on product description and tag.
* purchase and add to cart and wishlist behaviour of user provide the specific item to be used to profile user
*/
class UserProfiler extends DBh

{
  const WEIGHTTHRESHOLD = 3;  //max token weight
  // build user profile
  public function buildUserProfile($user_id, $p_id){
    $existingProfiling = array();
    // get user profile and check if p_id alreay exist then dont built profile from that item
    $profiles = $this->getUserProfile($user_id);
    $buildUserProfileFlag = 0;
    foreach ($profiles as $key => $profile) {
      $existingProfiling = json_decode($profile['profile'],true);
      $itemCatBrand = explode(',',$profile['itemID_cat_brand']);
      $itemCatBrand = explode('::',$key);
      if(isset($itemCatBrand[0])){
        $item_ids =  explode(',',$itemCatBrand[0]);
      }
      if(isset($itemCatBrand[1])){
        $category_ids =  explode(',',$itemCatBrand[1]);;
      }
      if(isset($itemCatBrand[2])){
        $brand_ids = explode(',',$itemCatBrand[2]);;
      }
      //if product have previously been profiled skip profilling // id check
      if(in_array($p_id,$item_ids)){
        $buildUserProfileFlag = 1;
      }
    }

    if($buildUserProfileFlag == 0){
      //$tags =DictionaryLookUp::requestAllSynonyms($product['p_keyword'],$noOfSynonysPerword);
      $productObj = new ProductController();
      $productResult = $productObj->getProduct($p_id);
      $stopWord = getStopwordsFromFile();
      foreach ($productResult as $key => $product) {
        $brand = $product['brand'];
        $category = $product['categories'];
        $ItemTags = processContent($stopWord, $product['p_keyword']);
        $ItemDescription =  processContent($stopWord, $product['description']);
      }
      $ItemTagArr = preg_split('/[\s,]+/', $ItemTags);
      $ItemDescriptionArr = preg_split('/[\s,]+/', $ItemDescription);
      $newTokens = array();
      foreach ($ItemTagArr as $key => $value) {
        $newTokens[$value] = 1; // make $ItemTagArr key value pair
      }
      foreach ($ItemDescriptionArr as $token) {
        if(array_key_exists($token,$newTokens)){ //increase tag weight if tag apear in product description
          $weight = $newTokens[$token];
          $newTokens[$token] = $weight+1;
        }
      }
      foreach ($newTokens as $token => $weight) {
        $current_profiling[] = array(
          'token' => $token,
          'weight'=> $weight
        );
      }

      //check if user profile exist for update or insert
      if(count($profiles) == 0){
        $itemCatBrand = $p_id.'::'.$category.'::'.$brand;
        $current_profiling;
        $updated_time = date("Y-m-d h:i:s", time());
        $encodeProfilling = json_encode($finalCurrent_profile);
        $this->insertProfile($user_id, $itemCatBrand, $encodeProfilling,$updated_time);
      }else{
        $_ids = implode(',', $item_ids);
        $final_ItemID = $_ids.','.$p_id;
        $final_CatID = implode(',',$category_ids);
        $final_BrandID = implode(',',$brand_ids);
        //check if brand or category is already present
        if(!in_array($category,$category_ids)){
          $final_CatID = $category.','.$final_CatID;
        }

        if(!in_array($brand,$brand_ids)){
          $final_BrandID = $brand.','.$final_BrandID;
        }
        $ItemIDCatBrand = $final_ItemID.'::'.$final_CatID.'::'.$final_BrandID;
        // $Finalcurrent_profile[$ItemIDCatBrand] = $current_profiling;

        $this->UpdateUserProfileComputation($user_id, $ItemIDCatBrand,$existingProfiling,$current_profiling);
      }
    }
  }
  // update user existing profile
  private function UpdateUserProfileComputation($user_id, $ItemIDCatBrand,$existingProfiling,$current_profiling){
    $item_match = 0;
    $new_profile = array();
    $token_match = 0;
    $existingProfiling = $existingProfiling;
    //if token already exist in profile increase weight of that existing token else add as new token
    foreach ($current_profiling as $key => $profileArr) {
      foreach ($existingProfiling as $pkey => $profile){
        if($profile['token'] == $profileArr['token']){
          if($profile['weight']+0 < self::WEIGHTTHRESHOLD){
            $profile['weight'] = $profile['weight'] + $profileArr['weight'];
          }else{
            $profile['weight'] = self::WEIGHTTHRESHOLD;
          }
          $existingProfiling[$pkey] = $profile;
          $token_match = 1;
        }
      }
      if($token_match == 0 ){
        $new_profile[] = $profileArr;
      }
      $token_match = 0;
    }
    $final_profile = array_merge($new_profile,$existingProfiling);
    $profile_json = json_encode($final_profile);
    $updated_time = date("Y-m-d h:i:s", time());
    $this->updateProfile($user_id, $ItemIDCatBrand, $profile_json,$updated_time);
  }

  // get user profile
  public function getUserProfile($user_id){
    $sql ="SELECT * FROM user_profile WHERE userID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$user_id]);
    $result = $myQuerry->fetchAll();
    return $result;
  }
  // get all user profiles
  public function getAllUserProfile(){
    $sql ="SELECT * FROM user_profile";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute();
    $result = $myQuerry->fetchAll();
    return $result;
  }
  // insert user profile
  private function insertProfile($user_id, $ItemIDCatBrand, $user_profile,$updated_time){
    $sql = "INSERT INTO user_profile (userID,last_updated,profile) VALUES (?,?,?,?)";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$user_id,$ItemIDCatBrand,$updated_time,$user_profile]);
  }

  //update user profile
  private function updateProfile($user_id, $ItemIDCatBrand,$profile_json,$updated_time){
    $sql ="UPDATE user_profile SET itemID_cat_brand, profile = ?, last_updated = ? WHERE userID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$ItemIDCatBrand, $profile_json,$updated_time,$user_id]);

  }
}
