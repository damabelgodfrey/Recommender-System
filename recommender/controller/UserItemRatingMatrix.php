<?php
/**
 * Computes user rating matrix, normalisedMeanRatingMatrix and average ratings for users
 *@return item rating matrix for current or all registered users
 */
class UserItemRatingMatrix {
  //Compute rating matrix
  public static function createRatingMatrix($type,$userID){
     $ratingCObj = new RatingController();
     if ($type =='AllUser') {
       $allRatingsQ = $ratingCObj->getAllRatings();
     }else{
       $allRatingsQ = $ratingCObj->getRatings($userID);
     }
     $UserRatingMatrix = array();
     foreach($allRatingsQ as $allRatings){
       $userRatings = $allRatings['product_rating'];
       $user_id   =  $allRatings['userID'];
       $product_rating = json_decode($userRatings,true);
       foreach ($product_rating as $p_rating){
         $p_id     =  $p_rating['product_id'];
         $rating =  $p_rating['rating'];
         $UserRatingMatrix[$user_id][$p_id]= $rating;
       }
     }
     return $UserRatingMatrix;
 }
//transform rating for item based CF
  public static function TransformedMatrix($itemUserRatingMatrix){
    $transposedMatrix = array();
    foreach($itemUserRatingMatrix as $User => $UserItemRating){
        foreach($UserItemRating as $item => $rating)
        {
          $transposedMatrix[$item][$User] = $rating;
        }
    }
    return $transposedMatrix;
    }

 //compute user mean rating for all users
 public static function computeUserMeanRatings($matrix) : array{
    $UserMeans = array();
    foreach ($matrix as $userID => $ItemRatingArray) { //for each user
      $ratings = 0;
      $counts = 0;
     foreach ($ItemRatingArray as $itemID => $rating) {
       $ratings += $rating ;
       $counts++;
     }
     if($counts != 0){
       $UserMeans[$userID] = $ratings/$counts;
     }else{
       $UserMeans[$userID] = 0;
     }
    }
    return $UserMeans;
  }

  //create a normalised user rating matrix
  public static function normalisedMeanRatingMatrix($user_id) : array{
    $matrix = self::createRatingMatrix("AllUser", $user_id);
    $NormalisedMatrix = array();
    $UserMeans = self::computeUserMeanRatings($matrix);
    foreach ($matrix as $userID => $ItemRatingArray) { //for each user
     foreach ($ItemRatingArray as $itemID => $rating) {
       $NormalisedMatrix[$userID][$itemID]= $rating - $UserMeans[$userID];
     }
    }
    return $NormalisedMatrix;
  }


   public static function createRatingMatrix2($type,$userID){
      $ratingCObj = new RatingController();
      if ($type =='AllUser') {
        $allRatingsQ = $ratingCObj->getAllRatings();
      }else{
        $allRatingsQ = $ratingCObj->getRatings($userID);
      }
      $UserRatingMatrix = array();
      foreach($allRatingsQ as $allRatings){
        $userRatings = $allRatings['product_rating'];
        $product_rating = json_decode($userRatings,true);
        foreach ($product_rating as $p_rating){
         $id     =  $p_rating['product_id'];
         $user_id   =  $allRatings['userID'];

         $Obj = new ProductController();
         $p = $Obj->getProduct($id);
         $p_title = $p[0]['title'];

         $obj2 = new UserController();
         $u = $obj2->selectUser("customer",$user_id);
         $u_name = $u[0]['username'];
          $rating =  $p_rating['rating'];
          $UserRatingMatrix[$u_name][$p_title]= $rating;
        }
      }
      debugfilewriter("\nUser Item Rating Matrix\n");
      debugfilewriter($UserRatingMatrix);
      self:: TransformedMatrix($UserRatingMatrix);
      return $UserRatingMatrix;
  }
}
