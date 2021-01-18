<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/Model/Products.php';
/**
 *
 */
  class ProductController extends Products{
    // fetch group products from product table
    public function requestGroupProduct($recommendedArray){
    $productIDs = array();
      if(isset($recommendedArray)){
        foreach($recommendedArray as $item =>$predictionValue){
        if($predictionValue > 0.01 && (int)sanitize($_POST['id']) != $item){
          $productIDs[] = $item;
        }
       }
       $ids = implode(',',$productIDs);
       return $this->getGroupProduct($ids);
      }else{
        return $productIDs;
      }
    }

    //get sets of brand
    public function getBrandSet($brandArr){
      $brandIDs = implode(',', $brandArr);
      $sql = "SELECT * FROM brand WHERE id IN ($brandIDs)
      ORDER BY FIELD(id, $brandIDs)";
      $myQuerry = $this->getConnection()->prepare($sql);
      $myQuerry->execute();
      $recommended = $myQuerry->fetchAll();
      return $recommended;
    }

    //get sets of categories
    public function getCategorySet($categoryArr){
      $categoryIDs = implode(',', $categoryArr);
      $sql = "SELECT * FROM categories WHERE id IN ($categoryIDs)
      ORDER BY FIELD(id, $categoryIDs)";
      $myQuerry = $this->getConnection()->prepare($sql);
      $myQuerry->execute();
      $recommended = $myQuerry->fetchAll();
      return $recommended;
    }
    public function updateAveProductRating($newAvgRating,$rating_counter,$product_id){
        $sql ="UPDATE products SET product_average_rating = ?, product_rating_counter = ? WHERE id = ?";
        $this->setProductAveRating($sql,$newAvgRating,$rating_counter,$product_id);

    }
}
