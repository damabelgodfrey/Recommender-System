<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DB_PDO.php';
/**
 * Class handles all request from wishlist view
 *  handles all wishlist operations.
 */

class WishListRepoController extends DB_PDO{

  public function insertWishlist($items_json, $user_name,$exp_time){
    $sql = "INSERT INTO wishlist (items,username,expire_date) VALUES (?,?,?)";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$items_json, $user_name,$exp_time]);
    return $myQuerry->lastInsertId();
  }

  public function updateWishlist($items_json,$cart_expire,$user_name){
    $sql ="UPDATE wishlist SET items = ?, expire_date = ? WHERE username = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$items_json,$cart_expire,$user_name]);
  }

  public function deleteWishlist($user_name){
    $sql = "DELETE FROM wishlist WHERE username = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$user_name]);
  }
  public function selectWishlist($input){
    if(is_int($input)){
      $sql = "SELECT * FROM wishlist WHERE id = ?";
    }else{
      $sql = "SELECT * FROM wishlist WHERE username = ?";
    }
   $myQuerry = $this->getConnection()->prepare($sql);
   $myQuerry->execute([$input]);
   $results = $myQuerry->fetchAll();
   return $results;
  }
}
