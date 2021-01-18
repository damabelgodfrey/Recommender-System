<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/controller/ContentBasedInit.php';
if (isset($_SERVER['HTTP_REFERER'])){
  $_SESSION['one_back'] = $_SERVER['HTTP_REFERER']?: NULL;
  $_SESSION['two_back'] = $_SESSION['one_back'] ?: NULL;
  $path = $_SESSION['one_back'];
  $path2 = $_SESSION['two_back'];
}else{?>
  <div class="bg-danger">
    <p class="text-center text-danger">
      Error!! That navigation pattern is forbidden!
    </p>
  </div>
  <a href="recommender" class="btn btn-lg btn-default">Return</a>

  <?php
  //exit;
}

?>
<p></p>
<div class="welcome">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="content">
          <h2>Recommender System</h2>
          <p>Click on function to run...</p>
        </div>
      </div>
    </div>
  </div>
</div>
<p><div class ='rec_errors bg-danger text-danger' id ='rec_errors'></div></p>
<p><div class ='rec_success bg-success text-success' id ='rec_success'></div></p>
<p></p><br><br>
<div class="box">
  <div class="row">
    <div class="col-md-12">
      <div class="content">
        <div class="col-md-4">
          <h3>
            <button class="btn btn-lg btn-warning pull-left" id="buildItemProfile" onclick="recommenderRequest('buildItemProfile','<?=$staff_id;?>');"><span class="fa fa-support"> Build Item Profile</span></button>
          </h3>

        </div>
        <div class="col-md-4">
          <h3>
            <button class="btn btn-lg btn-info pull-right" id="computeAveRating" onclick="recommenderRequest('computeAveRating','<?=$staff_id;?>');"><span class="fa fa-support"> Run Average User rating Computation</span></button>
          </h3>
        </div>
        <div class="col-md-4">
          <h3>
            <button class="btn btn-lg btn-primary pull-right" id="hybrid" onclick="recommenderRequest('hybridRecommenderEngine','<?=$staff_id;?>');"><span class="fa fa-support"> Run Hybrid Recommender</span></button>
          </h3>
        </div>

      </div>
    </div>
  </div>
</div>
<p></p><br><br>
<div class="box">
  <div class="row">
    <div class="col-md-12">
      <div class="col-md-4">
        <h3>
          <button class="btn btn-lg btn-primary pull-left" id="contentBasedRecommender" onclick="recommenderRequest('contentBasedRecommender','<?=$staff_id;?>');"><span class="fa fa-wrench"> Run Content Based Recommeder</span></button>
        </h3>
      </div>
      <div class="col-md-4">
        <h3>
          <button class="btn btn-lg btn-primary" id="itemBasedCF" onclick="recommenderRequest('runItemBasedCF','<?=$staff_id;?>');"><span class="fa fa-wrench "> Run Item Based CF Recommender</span></button>
        </h3>
      </div>
      <div class="col-md-4">
        <h3>
          <button class="btn btn-lg btn-primary pull-right" id="userBasedCF" onclick="recommenderRequest('runUserBasedCF','<?=$staff_id;?>');"><span class="fa fa-wrench"> Run User Based CF Recommender</span></button>

        </h3>

      </div>
    </div>
  </div>
</div>
<div class="col-xs-12">

</div>
<p><div class ='ajaxloading' id ='ajaxloading'></div></p>
<style>
.ajaxloading{
  position: fixed;
  max-width: 400px;
  max-height: 400px;
  background-image: url(../images/headerlogo/Loadingmodal.gif);
  background-repeat: no-repeat;
  top:0;
  right:0;
  bottom: 0;
  left: 0;
  z-index: 10000;
  margin: auto;
  display: none;
}
</style>
<div class="">

  <script type="text/javascript">
  jQuery('document').ready(function(){
    $('#ajaxloading').hide();
  });

  function recommenderRequest(requestType,user_id){
    $('#ajaxloading').show();
    jQuery('#rec_errors').html("");
    jQuery('#rec_success').html("");
    $('.btn').prop('disabled', true);
    var error = '';
    var request = {'requestType' : requestType, "userID" : user_id};
    jQuery.ajax({
      url : '/ecommerce/recommender/controller/RecEngineViewController.php',
      method : 'POST',
      data : request,
      success : function(data){
        if(data != 'Success..'){
          jQuery('#rec_errors').html(data);
          $('#ajaxloading').hide();
          $('.btn').prop('disabled', false);
        }
        if(data == 'Success..'){
          $('#ajaxloading').hide();
          $('.btn').prop('disabled', false);
          jQuery('#rec_success').html("Run Ruccessfully");
        //  location.reload();
        }
      },
      errors : function(){alert("Something Went Wrong!");},

    });
  }
  </script>
