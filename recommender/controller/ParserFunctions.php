<?php
//write output to file
function debugfilewriter($result2file){
  $mystopwordFile = $_SERVER['DOCUMENT_ROOT']."/ecommerce/files/debuggerfile.txt";
  file_put_contents($mystopwordFile, print_r($result2file, true), FILE_APPEND | LOCK_EX);
}

//return number in two decimal places
function to2Decimal($value){
  return sprintf('%0.2f', $value);
}

function toDecimal($value,$no){
  return sprintf('%0.'.$no.'f', $value);
}
//get getrusage
function my_getrusage($rustart,$rend){
  echo "This process used " . rutime($rend, $rustart, "utime") ." ms for its computations\n";
  echo "It spent " . rutime($rend, $rustart, "stime") ." ms in system calls\n";
}

//read stopword from file into an array
//@return an array of stop words
function getStopwordsFromFile(): array{
  $mystopwordFilePath = $_SERVER['DOCUMENT_ROOT']."/ecommerce/files/stopwords.txt";
  $stopword_array = array();
  if(file_exists($mystopwordFilePath)){
    try {
      $file_handle = fopen($mystopwordFilePath, "r");
      $theData = fread($file_handle, filesize($mystopwordFilePath));
      $my_array = explode(",", $theData);
      foreach($my_array as $stopword){
        $stopword_array[] = trim($stopword,"'");
      }
    } catch (\Exception $e) {
      $error =  $e->getMessage();
      debugfilewriter($error);
    }
   finally {
      fclose($file_handle);
      return $stopword_array;
   }
 }else{
   debugfilewriter("Error: File exist function of stopwords.txt file return false");
   return $stopword_array;
 }
}
// remove common word from inputed $contentAtribute tokens.
// perform porter steaming on inputed word by reducing words to their core root
//@ return processed content attribute
function processContent($stopword_array,$contentAtribute) : string{
  $stemmed_parts = array();
  $prepare_content = preg_replace('/\d+/u', '', $contentAtribute); //remove digits
  $prepare_content = preg_split('/[^[:alnum:]]+/', strtolower($prepare_content));
  $item_string =implode(' ',array_unique($prepare_content));
  $stopword_string = implode('|',$stopword_array);
  $content_ = preg_replace('/\b('.$stopword_string.')\b/','',$item_string); //remove stop words from content attribute
  $content_arr = explode(' ',$content_);
  foreach ($content_arr as $word) {
    //https://tartarus.org/martin/PorterStemmer/php.txt
    $stemmed_word = PorterStemmer::Stem($word);
    $stemmed_parts[] = $stemmed_word;
  }
  //replace double spacing with single spacing and duplicate token
  $stemmed_partStr = implode(' ', array_unique($stemmed_parts));
  $processed = preg_replace('/\s+/', ' ', $stemmed_partStr);
  return $processed;
}

//get script run tie and system usage
function rutime($ru, $rus, $index) {
    return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
     -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
}

//This function update wish list and cart.
function cart_wishlist_update($mode,$db,$item,$cart_id,$user_id,$json_update,$cart_expire,$available){
  if($mode == 'wishlist'|| $mode == 'wish'){
    $wishlistRepObj = new WishlistRepoController();
    $cartQ = $wishlistRepObj->selectWishlist($user_id);
  }else{
    $CartRepObj = new CartRepoController();
    $cartQ = $CartRepObj->selectCart($user_id);
  }
  $return = count($cartQ);
  if($return != 1){
    $cart_expire = date("Y-m-d H:i:s",strtotime("+30 days"));
    $exp_time = time();
      if($mode != 'wishlist'){
        $items_json = json_encode($item);
        if($mode == 'cart'){
          $cart_id=  $CartRepObj->insertCart($items_json, $user_id, $cart_expire, $exp_time);
          $_SESSION['success_flash'] = ' Item added to Cart successfully.';
          $_SESSION['cartid'] = $cart_id;
        }else{
          $wishlistRepObj->insertWishlist($items_json,$user_id,$cart_expire);
          $_SESSION['success_flash'] = ' Item added to Wish List successfully.';
        }
      }else{
       $wishlistRepObj->insertWishlist($items_json,$user_id,$cart_expire);
       $_SESSION['success_flash'] =  'wishlist update successful..';
      }
  }else{
    foreach ($cartQ as $cart) {
      $previous_items = json_decode($cart['items'],true); //makes it an associated array not an object
      $item_match = 0;
      $new_items = array();
      foreach ($previous_items as $pitem){
        if($item[0]['id'] == $pitem['id'] && $item[0]['size'] == $pitem['size']){
          if($mode == 'cart'){
              if($available == 0){
              $pitem['quantity'] = $pitem['quantity']; // do not update quantity for same item
            }else{
              $pitem['quantity'] =$pitem['quantity'] + $item[0]['quantity'];
            }
          }else{
            $pitem['quantity'] =$pitem['quantity'] + $item[0]['quantity'];
          }
         $item_match = 1;
        }
        $new_items[] = $pitem;
       }
    if($item_match != 1){
      $new_items = array_merge($item,$previous_items);
    }
    $items_json = json_encode($new_items);
    $cart_expire = date("Y-m-d H:i:s",strtotime("+30 days"));
    $exp_time = time();

      if($mode == 'cart'){
          $CartRepObj->updateCart($items_json,$cart_expire,$exp_time,$user_id);
         $_SESSION['success_flash'] =  'cart update successful..';
      }else{
        $wishlistRepObj->updateWishlist($items_json,$cart_expire,$user_id);
        $_SESSION['success_flash'] =  'wishlist update successful..';
      }
  }
}
  $rating = new RatingController();
  $rating->RateProduct($item[0]['id'],CART_WISH_RATING,$user_id,'cart_wish');
  $profilling = new UserProfiller();
  $profilling->buildUserProfile($user_id, $item[0]['id']);
}
