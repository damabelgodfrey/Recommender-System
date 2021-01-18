<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/Model/Transactions.php';
/**
 *Handles all transaction communication and operations
 * interact with transaction model and respond to transaction request
 */

class TransactionController extends Transactions
{

  public function getUserTransactions($userEmail){
    $idArray = array();
    $userTQuery = $this->getTransaction($userEmail);
    foreach($userTQuery as $userT):
      $items = json_decode($userT['items'],true);
      foreach ($items as $item) {
        $idArray[$item['id']] = 0;
      }
    endforeach;
    return $idArray;
  }
}
