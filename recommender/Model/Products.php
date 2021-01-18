<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DBh.php';
class Products extends DBh{

  public function getProduct($id){
    $sql = "SELECT * FROM products WHERE id = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$id]);
    $results = $myQuerry->fetchAll();
    return $results;
  }

  public function getAllProducts(){
    $sql = "SELECT * FROM products";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute();
    $results = $myQuerry->fetchAll();
    return $results;
  }

  protected function getGroupProduct($ids){
       $sql = "SELECT * FROM products WHERE id IN ($ids)
       ORDER BY FIELD(id, $ids)";
       $myQuerry = $this->getConnection()->prepare($sql);
       $myQuerry->execute();
        $recommended = $myQuerry->fetchAll();
        return $recommended;
  }
  protected function insertProducts($user_name,$updated_time,$rating_json){
    $sql = "INSERT INTO products (username,last_updated,product_rating) VALUES (?,?,?)";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$user_name,$updated_time,$rating_json]);
  }

  protected function updateProducts($rating_json,$updated_time, $user_name){
    $sql ="UPDATE products SET product_rating = ?, last_updated = ? WHERE username = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$rating_json, $updated_time, $user_name]);
  }

  protected function setProductAveRating($sql,$newAvgRating,$rating_counter,$product_id){
      $myQuerry = $this->getConnection()->prepare($sql);
      $myQuerry->execute([$newAvgRating,$rating_counter,$product_id]);
  }


}
