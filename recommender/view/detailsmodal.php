<?php
//require_once '../core/init.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/ecommerce/core/init.php';
//$currenturl = basename($_SERVER['PHP_SELF']);
$id = (int)sanitize($_POST['id']);
if($id < 1){
  $url = 'index';
  if(isset($_SESSION['rdrurl'])){
    $url =   $_SESSION['rdrurl'];
  }
  error_redirect($url,'product detail could not load properly previously. try again');
  die('please go back. product details could not load properly');
}
$mode = sanitize($_POST['mode']);
$sql = "SELECT * FROM products WHERE id = '$id'";
$result = $db->query($sql);
$product = mysqli_fetch_assoc($result);
$sizes = sizesToArray($product['sizes']);
$brand_id = $product['brand'];
$sql = "SELECT brand FROM brand WHERE id ='$brand_id'";
$brand_query = $db->query($sql);
$brand = mysqli_fetch_assoc($brand_query);
$tag = $product['p_keyword'];
?>

<!--Product Details light Box -->
<?php ob_start(); ?>
<div class="modal fade details-1" id="details-modal" tabindex="-1" role="dialog" aria-labelledby="details-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-header">
    <button class="close " type="button" onclick = "closeModal()" aria-label="close">
      <span aria-hidden="true">&times;</span>
    </button>
    <h3 class="modal-title text-center"><?= $product['title']; ?></h3>
  </div>
  <div class="modal-body">
    <div class"container-fluid ">
      <div class="row">
        <div class=" fotorama col-md-6"
        data-maxheight="400">
        <?php $photos = explode(',', $product['image']); //multiple image is seperated by ,
        foreach($photos as $photo): ?>
        <img src="<?= $photo; ?>" alt="<?= $product['title']; ?>" class="detailmodal-img img-responsive">
      <?php endforeach; ?>
    </div>
    <div class="col-md-6">
      <h4>Product Details</h4>
      <?php ?>
      <p><?= nl2br($product['description']); ?><p> <hr><!--nl2br is to preserve line break-->
        <p><strong>Brand:</strong> <?= $brand['brand']; ?></p> <hr>
        <form action="add_cart" method="post" id="add_product_form">
          <input type="hidden" name="product_id" value="<?=$id;?>">
          <input type="hidden" name="available" id="available" value="">
          <input type="hidden" name="price" id="price" value="">
          <input type="hidden" name="dmode" id="dmode" value="" class= "form-control">
          <div class="row">
            <div class="form-group col-xs-6">
              <label for="size">Size:</label>
              <select name="size" id="size" class="form-control">
                <option value="">Choose Size</option>
                <?php foreach ($sizes as $msize){
                  $size = $msize['size'];
                  $price =$msize['price'];
                  $available =$msize['quantity'];
                  if($available <=0){
                    echo '<option value="'.$size.'" data-price="'.$price.'" data-available="'.$available.'">'.$size.' ( out of stock)</option>';
                  }elseif($available < 20){
                    echo '<option value="'.$size.'" data-price="'.$price.'" data-available="'.$available.'">'.$size.' ('.$available.' Remaining)</option>';
                  }else{
                    echo '<option value="'.$size.'"  data-price="'.$price.'" data-available="'.$available.'">'.$size.' ('. 'Available)</option>';
                  }
                }?>
              </select>
            </div>
            <div class="form-group col-xs-6">
              <label for="pricelabel">Price: </label><input type="label" class="form-control" name="pricelabel" id="pricelabel" value="" placeholder="Choose Size to view price!" readonly>
            </div>
          </div>
          <div class="row">
            <div class="form-group col-xs-2">
              <label for="quantity">Quantity:</label>
              <input type="number" class="form-control" id="quantity" value="1"name="quantity" min="1" placeholder="Enter Quantity">
            </div>
            <?php if(check_permission('editor')){ ?>

              <div class="form-group col-xs-2">
                <label for="discount">Discount:</label>
                <input type="number" class="form-control" id="discount" value="0"name="discount" min="0" placeholder="Enter discount">
              </div>
            <?php }else{?>
              <input type="hidden" class="form-control" id="discouunt" value="0"name="discount" min="0" placeholder="Enter discount">

            <?php } ?>

            <div class="form-group col-xs-8">
              <label for="description">Request:</label>
              <textarea type "hidden" id="request" name="request"  class="form-control" pull-left maxlength="50" placeholder="Optional! Enter preference or special request e.g fragrance, colour etc" rows="2"></textarea>
            </div>
          </div>
          <span class="modal_errors" class="bg-danger"></span>

          <?php   include $_SERVER['DOCUMENT_ROOT'].'/ecommerce/includes/ratingDrawing.php'; ?>
        </form>

      </div>
    </div>

    <!--footer of the product detail pop up-->
    <div class="modal-footer">
      <?php if(is_logged_in()){ ?>
        <?php if($mode =='add'){ ?>
          <span class="modal_errors" class="bg-danger"></span>
          <button class="btn btn-success" id="addC" onclick="add_to_cart('cart');return false;"><span class="glyphicon glyphicon-shopping-cart"></span>Add TO Cart</button>
          <button class="btn btn-info" onclick="add_to_cart('wish');return false;"><span class="glyphicon glyphicon-heart"></span>Add TO wishlist</button>
          <script type="text/javascript">
          $("#details-modal").draggable({
            handle: ".modal-header"
          });
          </script>
        <?php } ?>
        <?php if ($mode=='view'): ?>
          <div class="bg-danger">
            <p class="text-center text-warning">
              Item Details view only!
              <button class="close " type="button" onclick = "closeModal()" aria-label="close">
                <span aria-hidden="true">&times;</span>
              </button>
            </p>
          </div>
        <?php endif; ?>

      <?php  }else{?>
        <div class="bg-danger text-center text-warning">
          <a href="login" class="btn btn-warning btn-sm" role="button">Login</a> to add and view Shopping Bag and saved Item!
        </div>
      <?php  } ?>
    </div>

    <?php if(is_logged_in()):
      include $_SERVER['DOCUMENT_ROOT'].'/ecommerce/recommender/view/RecommenderedItemView.php';
    endif;
    ?>
  </div>
  <script>

  jQuery('#size').change(function(){
    var available = jQuery('#size option:selected').data("available");
    jQuery('#available').val(available);
    var price = jQuery('#size option:selected').data("price");
    jQuery('#price').val(price);
    if(typeof price === 'undefined'){
      jQuery('#pricelabel').val('choose size');
    }else{
      var pricelabel = '₦';
      jQuery('#pricelabel').val(pricelabel+=price);
    }
  });

  $(function() {
    $('.fotorama').fotorama({'loop':true,'autoplay':true});
  });
  function closeModal(){
    jQuery('#details-modal').modal('hide');
    setTimeout(function(){
      jQuery('#details-modal').remove();
      jQuery('.modal.backdrop').remove();
    },50);
    location.reload();
  }

  function add_to_cart(dmode){
    jQuery('.modal_errors').html("");
    jQuery("#dmode").val(dmode);
    var p_size = jQuery('#size').val();
    var p_quantity = parseInt(jQuery('#quantity').val());
    var p_available = parseInt(jQuery('#available').val());
    var p_discount = parseInt(jQuery('#discount').val());
    var p_price = parseInt(jQuery('#price').val());
    var p_request = jQuery('#request').val();
    var error = '';
    var data = jQuery('#add_product_form').serialize();
    if(p_size =='' || p_quantity =='' || p_quantity == 0){
      error += '<p class="text-danger bg-danger text-center">Please do choose a size and quantity!</p>';
      jQuery('.modal_errors').html(error);
      return;
    }else if(p_available < p_quantity){
      error += '<p class="text-danger bg-danger text-center">You added '+p_quantity+' quantity but there are less available in store at the moment.</p>';
      jQuery('.modal_errors').html(error);
      return;
    }else{
      var y = document.getElementById("addC");
      y.innerHTML = "Adding to Cart...";
      $('.btn').prop('disabled', true);
      jQuery.ajax({
        url : '/ecommerce/admin/parsers/add_cart.php',
        method : 'post',
        data : data,
        success : function(){
          location.reload();
        },
        error : function(){alert("Something went wrong adding product to to cart")}
      });
    }
  }
</script>
<?php echo ob_get_clean(); ?>
