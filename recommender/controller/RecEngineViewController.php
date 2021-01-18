<?php
//This file receive ajax post data from RecommenderedItemview page
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/helpers/helpers.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/CollaborativeFilteringInit.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ContentBasedInit.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/HybridRecommenderEngine.php';
if(isset($_POST['requestType'])){
  $result = false;
  $today =date("Y-m-d");
  define('RECOMMENDER_ENGINE_INTERVAL',$today);
  if($_POST['requestType'] == "buildItemProfile"){
       $obj = new ItemProfiler();
         $result = $obj->buildItemProfile();
         if(!$result){
           echo 'Profile Building was not Successfully completed.';
         }else{
         echo 'Success..';
         }
    }

    if($_POST['requestType'] == "computeAveRating"){

          $obj = new RatingController();
          $result = $obj->computeAllProductAverageRating();
           if(!$result){
             echo 'Average Rating Computation was not Successfully completed.';
           }else{
           echo 'Success..';
           }
      }

      if($_POST['requestType'] == "contentBasedRecommender"){
        $pobj = new PredictionController();
        $lastUpdated = $pobj->getLastRecommenderEngineRun();
        if(isset($lastUpdated[0])){
          if($lastUpdated[0]['cb_last_updated'] !== RECOMMENDER_ENGINE_INTERVAL){
            $result = ContentBasedInit::runContentBasedRecommendationEngine();
            if($result){
              echo 'Success..';
            }else{
              echo 'content based recommendation run failed on confirmation';
            }
          }else{
            echo 'Recommeder system interval for content based CF is not passed.'.' => '. RECOMMENDER_ENGINE_INTERVAL;
          }
        }

        }

        if($_POST['requestType'] == "runItemBasedCF"){
          $pobj = new PredictionController();
          $lastUpdated = $pobj->getLastRecommenderEngineRun();
          if(isset($lastUpdated[0])){
            if($lastUpdated[0]['itemcf_last_updated'] !== RECOMMENDER_ENGINE_INTERVAL){
              $result =CollaborativeFilteringInit::runItemBasedRecommendationEngine();
              if($result){
                echo 'Success..';
              }else{
                echo 'Item based collaborative Filtering run failed on confirmation\n';
              }
            }else{
              echo 'Recommeder system interval for item based CF is not passed.'.' => '. RECOMMENDER_ENGINE_INTERVAL;
            }
          }

          }

          if($_POST['requestType'] == "runUserBasedCF"){
            $pobj = new PredictionController();
            $lastUpdated = $pobj->getLastRecommenderEngineRun();
            if(isset($lastUpdated[0])){
              if($lastUpdated[0]['usercf_last_updated'] !== RECOMMENDER_ENGINE_INTERVAL){
                $result = CollaborativeFilteringInit::runUserBasedRecommendationEngine();
                if($result){
                  echo 'Success..';
                }else{
                  echo 'user based collaborative Filtering run failed on confirmation';
                }
              }else{
                echo 'Recommeder system interval for user Based CF is not passed.'.' => '. RECOMMENDER_ENGINE_INTERVAL;
              }
            }

            }
            if($_POST['requestType'] == "hybridRecommenderEngine"){
              $pobj = new PredictionController();
              $lastUpdated = $pobj->getLastRecommenderEngineRun();
              if(isset($lastUpdated[0])){
                if($lastUpdated[0]['hybrid_last_updated'] !== RECOMMENDER_ENGINE_INTERVAL){
                  $result = HybridRecommenderEngine::runHybridRecommendationEngine();
                  if($result){
                    echo 'Success..';
                  }else{
                    echo 'Item based collaborative Filtering run failed on confirmation\n';
                  }
                }else{
                  echo 'Recommeder system interval for item based CF is not passed.'.' => '. RECOMMENDER_ENGINE_INTERVAL;
                }
              }

              }

  }else {
    echo "unknown request";
  }
?>
