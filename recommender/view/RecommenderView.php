<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/CollaborativeFilteringInit.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ContentBasedInit.php';
//require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ItemProfiler.php';
if(is_logged_in()){
  if(check_permission('admin')){
  $run_interval = RECOMMENDER_ENGINE_INTERVAL;
  $pobj = new PredictionController();
  $lastUpdated = $pobj->getLastRecommenderEngineRun();
if(isset($lastUpdated[0])){
  if($lastUpdated[0]['cb_last_updated'] !== $run_interval){
    $obj = new ItemProfiler();
    $obj->buildItemProfile();
    $result = ContentBasedInit::runContentBasedRecommendationEngine();
    if($result){
      echo "content based recommendation run successfully\n";
    }else{
      echo "content based recommendation run failed on confirmation\n";
    }
  }

  if($lastUpdated[0]['itemcf_last_updated'] !== $run_interval){
    $result =CollaborativeFilteringInit::runItemBasedRecommendationEngine();
    if($result){
      echo "Item based collaborative Filtering run successfully\n";
    }else{
      echo "Item based collaborative Filtering run failed on confirmation\n";
    }
  }
  if($lastUpdated[0]['usercf_last_updated'] !== $run_interval){
    $result = CollaborativeFilteringInit::runUserBasedRecommendationEngine();
    if($result){
      echo "user based collaborative Filtering run successfully\n";
    }else{
      echo "user based collaborative Filtering run failed on confirmation\n";
    }
  }
 }
}
$predObj = new PredictionController();
$recommendedUserBasedCF = $predObj->getUserBasedCFRecommendation($user_id);
$recommendedItemBasedCF = $predObj->getItemBasedCFRecommendation($user_id);
$recommendedContentBased = $predObj->getContentBasedRecommendation($user_id);
}

$obj = new ProductController();
$return = 0;
if(isset($recommendedUserBasedCF) && $recommendedUserBasedCF != false){
  $recommendedUserBased = $obj->requestGroupProduct($recommendedUserBasedCF);
  $return = count($recommendedUserBased);
}
if($return > 0){ ?>
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading text-center"><h3>⇩ Similar Users Also Purchase ⇩</h3>
      </div>
      <div class="panel-body">
        <div class="posts_list">
          <?php foreach ($recommendedUserBased as $product) :
            $listP = (int)$product['list_price'];
            $actualP = (int)$product['price'];
            $perOff = ($listP - $actualP )/ $listP;
            $perOff = round($perOff * 100);
            $photos = explode(',',$product['image']);
            ?>
            <div class="col-xs-6 col-sm-5 col-md-4 padding-0 animation">
              <div class="polaroid text-center">
                <div class="product_title">
                  <h4><strong><?= $product['title']; ?></strong></h4>
                </div>
                <div class="imgHolder">
                  <img onclick="detailsmodal('add',<?= $product['id']; ?>)" src="<?= $photos[0]; ?>" alt="<?= $product['title']; ?>" class="img-thumb" style="width:100%"/>
                  <?php if ($product['sales'] == 1): ?>
                    <span>
                      <button type ="button" id="sales" class="btn btn-xs btn-danger pull-left" onclick="detailsmodal('add',<?= $product['id']; ?>)">Sales</button>
                    </span>
                  <?php endif; ?>
                </div>
                <p></p><p class="list-price"><s>$<?= $product['list_price']; ?></s></p>
                <strong> <p class="price text-danger">$<?= $product['price']; ?> (<?= $perOff ?>% off)</p></strong>
                <!--<button type ="button" id="dbutton" class="btn btn-sm btn-danger" onclick="detailsmodal(
                <?= $product['id']; ?>)">Details</button> -->
              </div>
            </div>
          <?php endforeach;
          ?>
        </div>
      </div>
      <div class="panel-footer"><?php echo $return; ?></div>
    </div>
  </div>
<?php }else{ ?>
  <div class="bg-info">
    <p class="text-center text-info">
      No recommendation made at this time!
    </p>
  </div>
<?php } ?>

<?php
$obj = new ProductController();
$return = 0;
if(isset($recommendedItemBasedCF) && $recommendedItemBasedCF != false){
  $ItemCFRecommended = $obj->requestGroupProduct($recommendedItemBasedCF);
  $return = count($ItemCFRecommended);
}
if($return > 0){ ?>
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading text-center"><h3>⇩ Recommendation Based on Item you have Rated Before ⇩</h3>
      </div>
      <div class="panel-body">
        <div class="posts_list">
          <?php foreach ($ItemCFRecommended as $product) :
            $listP = (int)$product['list_price'];
            $actualP = (int)$product['price'];
            $perOff = ($listP - $actualP )/ $listP;
            $perOff = round($perOff * 100);
            $photos = explode(',',$product['image']);
            ?>
            <div class="col-xs-6 col-sm-5 col-md-4 padding-0 animation">
              <div class="polaroid text-center">
                <div class="product_title">
                  <h4><strong><?= $product['title']; ?></strong></h4>
                </div>
                <div class="imgHolder">
                  <img onclick="detailsmodal('add',<?= $product['id']; ?>)" src="<?= $photos[0]; ?>" alt="<?= $product['title']; ?>" class="img-thumb" style="width:100%"/>
                  <?php if ($product['sales'] == 1): ?>
                    <span>
                      <button type ="button" id="sales" class="btn btn-xs btn-danger pull-left" onclick="detailsmodal('add',<?= $product['id']; ?>)">Sales</button>
                    </span>
                  <?php endif; ?>
                </div>
                <p></p><p class="list-price"><s>$<?= $product['list_price']; ?></s></p>
                <strong> <p class="price text-danger">$<?= $product['price']; ?> (<?= $perOff ?>% off)</p></strong>
                <!--<button type ="button" id="dbutton" class="btn btn-sm btn-danger" onclick="detailsmodal(
                <?= $product['id']; ?>)">Details</button> -->
              </div>
            </div>
          <?php endforeach;
          ?>
        </div>
      </div>
      <div class="panel-footer"><?php echo $return; ?></div>
    </div>
  </div>
<?php }else{ ?>
  <div class="bg-info">
    <p class="text-center text-info">
      No recommendation made at this time!
    </p>
  </div>
<?php }
$recommended = $obj->requestGroupProduct($recommendedContentBased); //fetch product recommended
$return = count($recommended);
if($return > 0){ ?>
        <div class="col-md-12">
        <div class="panel panel-default">
          <?php if(is_logged_in()) {
            ?> <div class="panel-heading text-center"><h3>⇩ Content Based => Based on previous item ⇩</h3> <?php
          }else {
            ?><div class="panel-heading text-center"><h3>⇩ You may also like ⇩</h3> <?php
          }?>

        </div>
        <div class="panel-body">
            <div class="posts_list">
                <?php foreach($recommended as $product) :
                $listP = (int)$product['list_price'];
                $actualP = (int)$product['price'];
                $perOff = ($listP - $actualP )/ $listP;
                $perOff = round($perOff * 100);
                $photos = explode(',',$product['image']);
                   ?>
                 <div class="col-xs-6 col-sm-5 col-md-4 padding-0 animation">
                   <div class="polaroid text-center">
                     <div class="product_title">
                       <h4><strong><?= $product['title']; ?></strong></h4>
                     </div>
                     <div class="imgHolder">
                       <img onclick="detailsmodal('add',<?= $product['id']; ?>)" src="<?= $photos[0]; ?>" alt="<?= $product['title']; ?>" class="img-thumb" style="width:100%"/>
                        <?php if ($product['sales'] == 1): ?>
                          <span>
                            <button type ="button" id="sales" class="btn btn-xs btn-danger pull-left" onclick="detailsmodal('add',<?= $product['id']; ?>)">Sales</button>
                          </span>
                       <?php endif; ?>
                     </div>
                    <p></p><p class="list-price"><s>$<?= $product['list_price']; ?></s></p>
                    <strong> <p class="price text-danger">$<?= $product['price']; ?> (<?= $perOff ?>% off)</p></strong>
                    <!--<button type ="button" id="dbutton" class="btn btn-sm btn-danger" onclick="detailsmodal(
                    <?= $product['id']; ?>)">Details</button> -->
                 </div>
                 </div>
               <?php endforeach;
             ?>
             </div>
    </div>
     <div class="panel-footer"><?php echo $return; ?></div>
    </div>
  </div>
<?php }else{ ?>
<?php } ?>
