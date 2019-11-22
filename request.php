  <?php


$eBayAPIURL_finding = '';//$_POST['eBayAPIURL_finding'];
$kw = $_POST['kw'];
$period = $_POST['period'];
 include_once 'includes/eBayFunctions.php';
 //echo ($catID . '<br>');
echo get_seller_sold_items($kw,$period);
 
?>