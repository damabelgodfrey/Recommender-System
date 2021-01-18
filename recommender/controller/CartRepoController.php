<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DBh.php';
class cartRepoController extends DBh{
  public function insertCart($items_json, $user_id,$cart_expire,$exp_time){
    $sql = "INSERT INTO cart (items,userID,expire_date,exp_time) VALUES (?,?,?,?)";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$items_json, $user_id,$cart_expire,$exp_time]);
    return $myQuerry->lastInsertId();
  }

  public function updateCart($items_json,$cart_expire,$exp_time,$user_id){
    $sql = "UPDATE cart SET items = ?, expire_date = ?, exp_time = ? WHERE userID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$items_json,$cart_expire,$exp_time,$user_id]);
  }

  public function deleteCart($user_id){
    $sql = "DELETE FROM cart WHERE userID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$user_id]);
  }
  public function selectCart($input){
    $sql = "SELECT * FROM cart WHERE userID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$input]);
    $results = $myQuerry->fetchAll();
    return $results;
  }
  public function selectAllCart(){
    $sql = "SELECT * FROM cart ORDER BY id DESC LIMIT 10";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute();
    $results = $myQuerry->fetchAll();
    return $results;
  }
}
