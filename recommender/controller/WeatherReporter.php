<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/UserController.php';
/**
 *
 */
class WeatherReporter
{
  private static $endPoint = "http://api.openweathermap.org/data/2.5/weather";
  private static $APIKey = "42e679a25c9b52325778910be51885e0";
  private static $user_id;
  private static $location = "London,uk";

  protected static function getUserLocation($user_id){
    $obj= new UserController();
    $user = $obj->selectUser("customer",$user_id);
    $country = "null";
    foreach ($user as $key => $u) {
      $country = $u['country'];
    }
    return $country;
   }

  public static function getWeatherReport($user_id=-1){
    if($user_id == -1){
      //get user location by gps or set default location
     $url="http://api.openweathermap.org/data/2.5/weather?q=London,uk&APPID=42e679a25c9b52325778910be51885e0";
    }else{
      $location = self::getUserLocation($user_id);
      self::$user_id = $user_id;
      $url = self::$endPoint.'?q='.$location.'&APPID='.self::$APIKey;
    }
     $json=file_get_contents($url);
     $data=json_decode($json,true);
     foreach ($data as $key => $value) {
       if($key == 'main'){
       $temp = $value['temp'];
       $maxTemp = $value['temp_max'];
       $minTemp = $value['temp_min'];
       $presure = $value['pressure'];
       $humidity = $value['humidity'];
       }
     }
     $season = self::getCurrentSeason();
    }

  public static function getCurrentSeason(){
    $currentMonth=(int)DATE("m");
    if($currentMonth>= 3 && $currentMonth<=5)
      $season = "spring";
    else if($currentMonth>= 6 && $currentMonth<= 8)
      $season = "summer";
    else if($currentMonth>= 9 && $currentMonth<=11)
      $season = "fall";
    else
      $season = "winter";
      return $season;
      }
    }
