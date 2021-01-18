<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DB_PDO.php';
/**
 *  * Class handles all request from wishlist view
  *  handles all wishlist operations.
 */
class Transactions extends DB_PDO
{

  public function getTransaction($user_email){
    $sql = "SELECT * FROM transactions WHERE email = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$user_email]);
    $results = $myQuerry->fetchAll();
    return $results;
  }

  public function getAllTransactions(){
    $sql = "SELECT * FROM transactions";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute();
    $results = $myQuerry->fetchAll();
    return $results;
  }
  public function setTransaction($chargeId, $cart_id,$name,$email,$address,$itemOrdered,$sub_total,$tax,$grand_total,$description,$tranType,$txn_date){
  $sql = "INSERT INTO transactions (charge_id,cart_id,full_name,email,address,items,sub_total,tax,grand_total,description,txn_type,txn_date) VALUES
  (?,?,?,?,?,?,?,?,?,?,?,?)";
  $myQuerry = $this->getConnection()->prepare($sql);
  $myQuerry->execute($chargeId, $cart_id,$name,$email,$address,$itemOrdered,$sub_total,$tax,$grand_total,$description,$tranType,$txn_date);
  }
}
