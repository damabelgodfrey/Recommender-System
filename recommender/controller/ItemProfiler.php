<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/DBh.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ProductController.php';
/**
* This class build item profile extracting relevant information from product repository and associated tables
*/
class ItemProfiler extends DBh {
  // compute item profile from product table
  public function buildItemProfile(){
    $noOfSynonysPerword = 2;
    $p_obj = new ProductController();
    $products = $p_obj->getAllProducts();
    $updated_time = date("Y-m-d h:i:s", time());
    $stopWord = getStopwordsFromFile();
    foreach ($products as $key => $product) {
      $tags =DictionaryLookUp::requestAllSynonyms($product['p_keyword'],$noOfSynonysPerword);
      $itemProfileTokens= processContent($stopWord, $tags);
        $profiling[$product['id']] = array(
            'tag' => $itemProfileTokens,
            'category'=> $product['categories'],
            'brand'=> $product['brand']
            );
    }
    //a multi insertion of all products to item profile table with built querry
    $query = 'REPLACE INTO item_profile VALUES ';
    $query_parts = array();
    foreach ($profiling as $itemID => $profile) {
      $query_parts[] = "('" . $itemID . "','" . json_encode($profile) . "', '" . $updated_time . "')";
    }
     $query .= implode(',', $query_parts);
     $myQuerry = $this->getConnection()->exec($query);
     return;
  }
  // prepare item profile to update or delete
  public function addUpdateItemProfile($operation, $item_id,$tag,$category,$brand){
    $updated_time = date("Y-m-d h:i:s", time());
    $profiling[] = array(
      'tag' => $tag,
      'category'=> $category,
      'brand'=> $brand
      );
    if($operation == "update"){
      $this->updateProfile($item_id,$profiling,$updated_time);
    }else {
      $this->insertProfile($item_id,$profiling,$updated_time);
    }
  }
  //get all item profiles with associated brand name and category name
  public function getItemProfile_Brand_Category(){
    $sql = "SELECT a.profile, a.itemID, e.average_rating, c.category, d.brand
            FROM item_profile a
            LEFT JOIN products b ON a.itemID = b.id
            LEFT JOIN categories c ON b.categories = c.id
            LEFT JOIN brand d ON b.brand = d.id
            LEFT JOIN average_ratings e ON e.itemID = b.id";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute();
    $result = $myQuerry->fetchAll();
    return $result;
  }
  // get item profile
  public function getItemProfile($item_id){
    $sql ="SELECT * FROM item_profile WHERE itemID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$item_id]);
    $result = $myQuerry->fetchAll();
    return $result;
  }
  //delete item profile
  public function deleteItemProfile($item_id){
    $sql ="DELETE FROM item_profile WHERE itemID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$item_id]);
  }
  // insert item profile
  private function insertProfile($item_id, $item_profile,$updated_time){
    $sql = "INSERT INTO item_profile (itemID,profile,last_updated) VALUES (?,?,?)";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$item_id, $item_profile,$updated_time]);
  }
  //update user profile
  private function updateProfile($id, $profiling, $updated_time){
    $sql ="UPDATE item_profile SET profile = ?, last_updated = ? WHERE itemID = ?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$profiling, $updated_time,$id]);
  }
}
