<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/Model/Users.php';
/**
 *
 */

class UserController extends Users
{
  public function updateUserLogin($userType,$date,$user_id){
    if($userType == "customer"){
     $sql ="UPDATE customer_user SET last_login = ? WHERE userID = ?";
    }else{
    $sql ="UPDATE staffs SET last_login = ? WHERE username = ?";
    }
   $myQuerry = $this->setUserLogin($sql,$date,$user_id);
  }
  public function selectUser($userType,$user_id){
    if($userType == "customer"){
      $sql ="SELECT * FROM customer_user WHERE id = ?";
    }else{
      $sql ="SELECT * FROM staffs WHERE id = ?";
    }
     return $this->getUser($sql,$user_id);
  }

public function selectUserByEmail($userType,$email){
  if($userType == "customer"){
    $sql ="SELECT * FROM customer_user WHERE email = ?";
  }else{
    $sql ="SELECT * FROM staffs WHERE email = ?";
  }
   $f = $this->getUserByEmail($sql,$email);
   return $f;
}
  public function updatepassword($userType, $new_hashedpwd,$user_id){
    if($userType == "customer"){
      $sql = "UPDATE customer_user SET password = '$new_hashed' WHERE id = ?";
    }else{
      $sql = "UPDATE staff SET password = '$new_hashed' WHERE id = ?";
    }
    $this->setPassword($sql,$new_hashedpwd,$user_id);
  }

  public function registerUser($username,$name,$phone,$email,$hashed,$permissions){
    $sql = "INSERT INTO customer_user (username,full_name,phone,email,password,permissions) values(?,?,?,?,?,?)";
     return $this->setUser($sql,$username,$name,$phone,$email,$hashed,$permissions);

  }

  public function updateStaff($username1,$name1,$phone1,$email1,$photopath,$permissions1,$rank,$last_login,$edit_id){
    $sql = "UPDATE staffs SET username =?, full_name =?, phone=?, email =?, photo =?, permissions = ?,last_login =? WHERE id=?";
    $this->setUpdatedStaff($sql,$username1,$name1,$phone1,$email1,$photopath,$permissions1,$rank,$last_login,$edit_id);
  }

  public function updateUserAddress($street,$street2,$state,$city,$zip_code,$phone,$country,$user_email){
    $sql = "UPDATE customer_user SET street =?, street2 =?, state = ?,city = ?, zip_code =?, phone=?, country = ?
    WHERE email=?";
    $myQuerry = $this->getConnection()->prepare($sql);
    $myQuerry->execute([$street,$street2,$state,$city,$zip_code,$phone,$country,$user_email]);
  }

  public function registerStaff($username,$name,$phone,$email,$hashed,$permissions,$ranks,$photopath){
    $sql ="INSERT INTO staffs (username,full_name,phone,email,password,permissions,rank,photo) values(?,?,?,?,?,?,?,?)";
    $this->setStaff($sql,$username,$name,$phone,$email,$hashed,$permissions,$ranks,$photopath);

  }

}
