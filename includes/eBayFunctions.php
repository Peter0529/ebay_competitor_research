<?php	

require_once('ebay_keys.php');


if(session_id() == '' || !isset($_SESSION)) {
    // session isn't started
   // session_start();
}

function get_categories() {
    global $token;

    $post_data = '<?xml version="1.0" encoding="utf-8"?>
<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  <RequesterCredentials>
    <eBayAuthToken>' . $token . '</eBayAuthToken>
  </RequesterCredentials>
  <CategorySiteID>0</CategorySiteID>
  <DetailLevel>ReturnAll</DetailLevel>
  <LevelLimit>1</LevelLimit>
  <ViewAllNodes>true</ViewAllNodes>
</GetCategoriesRequest>';
    $body = callapi($post_data, "GetCategories");
	echo $body;
    return $body;
}

function get_items_sold_count($catID, $apiEndPoint, $kw, $countryID,$itemIDs) {
    global $APPNAME;
	global $responseEncoding;
	global $eBayAPIURL;
   
    $apicalla  = "$apiEndPoint";
    $apicalla .= "callname=GetMultipleItems";
    $apicalla .= "&version=967";
	$apicalla .= "&siteid=$countryID";
    $apicalla .= "&appid=$APPNAME";
    $apicalla .= "&responseencoding=$responseEncoding";
    $apicalla .= "&includeSelector=Details";
    $apicalla .= "&ItemID=";

    $itemids_str = '';
    $i = 0;
    $ret = array();
    foreach($itemIDs as $item) {
        $i++;
        if($i % 20 == 0 || sizeof($itemIDs) == $i)
        {
            
            $request_str = $apicalla;
            $itemids_str .= $item;
            //$itemids_str = substr($itemids_str,0,-1);
            
            $request_str.=$itemids_str;
            
            
            $resp = simplexml_load_file($request_str);
            if ($resp) {
                // Set return value for the function to null
                $retna = '';
                
              // Verify whether call was successful 201028256493
              if ($resp->Ack == "Success") {
                
                foreach($resp->Item as $item){
                    #array_push($ret,$item->QuantitySold);
                    $ret[(string)$item->ItemID] = $item->QuantitySold;
                }
              }
            }
            
            $itemids_str = '';
            //$i = 0;
            //QuantitySold
        }
        else
        {
            $itemids_str .= $item.',';
        }
    }
    return $ret;
}

function get_siteID_Num($siteID)
{
	$num=array(
				"EBAY-US"=>"0",
				"EBAY-ENCA"=>"2",
				"EBAY-GB"=>"3",
				"EBAY-AU"=>"15",
				"EBAY-AT"=>"16",
				"EBAY-FRBE"=>"23",
				"EBAY-FR"=>"71",
				"EBAY-DE"=>"77",
				"EBAY-MOTOR"=>"100",
				"EBAY-IT"=>"101",
				"EBAY-NLBE"=>"123",
				"EBAY-NL"=>"146",
				"EBAY-ES"=>"186",
				"EBAY-CH"=>"193",
				"EBAY-HK"=>"201",
				"EBAY-IN"=>"203",
				"EBAY-IE"=>"205",
				"EBAY-MY"=>"207",
				"EBAY-FRCA"=>"210",
				"EBAY-PH"=>"211",
				"EBAY-PL"=>"212",
				"EBAY-SG"=>"216"
				);
				
	return $num[$siteID];
	
}
function get_categories_new($siteID) {
    
	global $APPNAME;
	global $responseEncoding;
	global $eBayAPIURL_shopping;


	$apicalla  = "$eBayAPIURL_shopping";
    $apicalla .= "callname=GetCategoryInfo";
    $apicalla .= "&appid=$APPNAME";
	$apicalla .= "&version=967";
	$apicalla .= "&siteid=" . get_siteID_Num($siteID) ;
    //$apicalla .= "&siteid=0";
    $apicalla .= "&CategoryID=-1"; 
	$apicalla .= "&IncludeSelector=ChildCategories";
    
    // Load the call and capture the document returned by eBay API
    $resp = simplexml_load_file($apicalla);
	
	 if ($resp) {
      // Set return value for the function to null
      $retna = '';
	  //$result = $xml->xpath("//CategoryArray/Category");
	   if ($resp->Ack == "Success") {
			
			echo ('<select class="form-control" name="catid" id="catid">');
			foreach ($resp->CategoryArray->Category as $cat) {
				
				$selected = '';
			if(isset($_SESSION["cat"]) and $_SESSION['cat'] == $cat->CategoryID) {
					$selected = ' selected="selected"'; 
				}
                echo '<option value="' . $cat->CategoryID . '"' . $selected . '>';
                if($cat->CategoryName == 'Root')
                {
                    echo ' ' . '</option>';
                }
                else{
                    echo $cat->CategoryName . '</option>';
                }
				
			}
			echo "</select>";
	   }
 
    } else {
      
    } 

    // Return the function's value
    return $retna;
   
}

function get_mostwatched($catID, $apiEndPoint) {
    global $token;
	global $APPNAME;
	global $responseEncoding;
	global $eBayAPIURL;


	$apicalla  = "$apiEndPoint";
    $apicalla .= "OPERATION-NAME=getMostWatchedItems";
    $apicalla .= "&SERVICE-VERSION=1.0.0";
    $apicalla .= "&CONSUMER-ID=$APPNAME";
    $apicalla .= "&RESPONSE-DATA-FORMAT=$responseEncoding";
    $apicalla .= "&maxResults=10";
    //$apicalla .= "&categoryId=$catID"; 
	$apicalla .="&categoryId=1217";
    
    // Load the call and capture the document returned by eBay API
    $resp = simplexml_load_file($apicalla);
   if ($resp) {
      // Set return value for the function to null
      $retna = '';

    // Verify whether call was successful
    if ($resp->ack == "Success") {

      // If there were no errors, build the return response for the function
      $retna .= "<h1>Top 3 Most Watched Items in the ";
      $retna .=  $resp->itemRecommendations->item->primaryCategoryName; 
      $retna .= " Category</h1> \n";

      // Build a table for the 3 most watched items
      $retna .= "<!-- start table in getMostWatchedItemsResults --> \n";
      $retna .= "<table width=\"100%\" cellpadding=\"5\" border=\"0\"><tr> \n";

      // For each item node, build a table cell and append it to $retna 
      foreach($resp->itemRecommendations->item as $item) {

        /* Set the cell color blue for the selected most watched item
        if ($selectedItemID == $item->itemId) {
          $thisCellColor = $cellColor;
        } else {
          $thisCellColor = '';
        }
		*/
        // Determine which price to display
        if ($item->currentPrice) {
        $price = $item->currentPrice;
        } else {
        $price = $item->buyItNowPrice;
        }

        // For each item, create a cell with imageURL, viewItemURL, watchCount, currentPrice
       // $retna .= "<td $thisCellColor valign=\"bottom\"> \n";
        $retna .= "<td><img src=\"$item->imageURL\"> \n";
        $retna .= "<p><a href=\"" . $item->viewItemURL . "\">" . $item->title . "</a></p>\n";
        $retna .= 'Watch count: <b>' . $item->watchCount . "</b><br> \n";
        $retna .= 'Current price: <b>$' . $price . "</b><br><br> \n";
        $retna .= "<FORM ACTION=\"" . $_SERVER['PHP_SELF'] . "\" METHOD=\"POST\"> \n";
        $retna .= "<INPUT TYPE=\"hidden\" NAME=\"Selection\" VALUE=\"$item->itemId\"> \n";
        $retna .= "<INPUT TYPE=\"submit\" NAME=\"$item->itemId\" ";
        $retna .= "VALUE=\"Get Details and Related Category Items\"> \n";
        $retna .= "</FORM> \n";
        $retna .= "</td> \n";
      }
      $retna .= "</tr></table> \n<!-- finish table in getMostWatchedItemsResults --> \n";
      
      } else {
          // If there were errors, print an error
          $retna = "The response contains errors<br>";
          $retna .= "Call used was: $apicalla";

    }  // if errors

    } else {
      // If there was no response, print an error
      $retna = "Dang! Must not have got the getMostWatchedItems response!<br>";
      $retna .= "Call used was: $apicalla";
    }  // End if response exists

    // Return the function's value
    return $retna;
   
	}
	
 
function get_mostwatched_keywords($catID, $apiEndPoint, $kw, $countryID) {
 
	global $APPNAME;
	global $responseEncoding;
    global $eBayAPIURL;
    global $eBayAPIURL_finding;
    $apiEndPoint = '';
    $apicalla  = $eBayAPIURL_finding;
    $apicalla .= "OPERATION-NAME=findItemsAdvanced";
    $apicalla .= "&SERVICE-VERSION=1.0.0";
	$apicalla .= "&GLOBAL-ID=$countryID";
    $apicalla .= "&SECURITY-APPNAME=$APPNAME";
    $apicalla .= "&RESPONSE-DATA-FORMAT=$responseEncoding";
    $apicalla .= "&REST-PAYLOAD";
    $apicalla .= "&paginationInput.entriesPerPage=50";
    if(strlen($kw) > 0){
	$apicalla .= "&keywords=$kw";
	}
	if($catID > 0){
    $apicalla .= "&categoryId=$catID";
	} 
	
	$apicalla .= "&sortOrder=WatchCountDecreaseSort";
	$apicalla .= "&outputSelector=PictureURLLarge";
	//$apicalla .="&categoryId=1217";
   //echo ($apicalla);
	
    // Load the call and capture the document returned by eBay API
    $resp = simplexml_load_file($apicalla);
   if ($resp) {
      // Set return value for the function to null
      $retna = '';
    $i =0; 
    $itemIDs = array();
    foreach($resp->searchResult->item as $item) {
        $itemIDs[$i++] = $item->itemId;
    }

    $sold_counts = get_items_sold_count($catID, "http://open.api.ebay.com/Shopping?", $kw, $countryID,$itemIDs);
    // Verify whether call was successful
    if ($resp->ack == "Success") {

      
      // For each item node, build a table cell and append it to $retna 
      foreach($resp->searchResult->item as $item) {
		
		$hot = '';
        // Determine which price to display
        if ($item->sellingStatus->currentPrice) {
			$price = $item->sellingStatus->currentPrice;
        } else {
			$price = $item->sellingStatus->convertedCurrentPrice;
        }
		
		$img = '';
		$str_count = isset($sold_counts[(string)$item->itemId])? $sold_counts[(string)$item->itemId]:' ';
		if ($item->pictureURLLarge) {
			$img = $item->pictureURLLarge;
        } else {
			$img = $item->galleryURL;
        }
		
		if($item->listingInfo->watchCount > 999){
			$hot = '<i style="margin-left:10px; font-size:40px; color:red;" class="fab fa-gripfire"></i>';

			//$hot = ' <span style="color:yellow;font-weight:bold;background-color:red">(&#10067;)</span>';
		}
        
		
		$retna .= '<div class="row">
				<div class="col-md-12">
					<div class="card">
					<div class="card-header">
                        <div class="card-title-wrap bar-success">
							<h4 class="card-title"><a href="' . $item->viewItemURL . '">' . $item->title . '</a></h4>
						</div>
					</div>
					<div class="card-body">
						<div class="row">
						<div class="col-md-1">&nbsp;</div>
						<div class="col-md-7">
							<div class="card-text"><span style="font-size:20px; font-weight:bold;">Price: $' . $price . '</span></div>
							<div class="card-text"><span style="font-size:20px; font-weight:bold;">Watch Count : ' . $item->listingInfo->watchCount .'</span>' . $hot . '</div>
                            <div class="card-text"><span style="font-size:20px; font-weight:bold;">Sold Count: ' . $str_count . '</span></div>
						</div>
						
						<div class="col-md-4">
							 
								<a href="'.$item->viewItemURL.'"><img class="img-responsive card-img img-fluid p-4" style="max-height:250px; max-width:250px;" src=' . $img . '></a>
						</div>
						 
							
						</div>			
					</div>
					</div>
			 
			</div>
		</div>';
		
		
		
      }
      
      } else {
          // If there were errors, print an error
          $retna = "No Search Result found.";
          
    }  // if errors

    } else {
      // If there was no response, print an error
      $retna = "No Search Result found.";
     
    }  // End if response exists

    // Return the function's value
    return $retna;
   
	}
	
 
	
function callapi($post_data, $call_name) {
	
    global $COMPATIBILITYLEVEL, $DEVNAME, $APPNAME, $CERTNAME, $SiteId, $eBayAPIURL;
    
	//Merchandising API
	$ebayapiheader = array(
		"EBAY-SOA-CONSUMER-ID: $APPNAME",
		"X-EBAY-SOA-OPERATION-NAME: getMostWatchedItems",
        "X-EBAY-SOA-REQUEST-DATA-FORMAT: XML",
		"X-EBAY-SOA-GLOBAL-ID: EBAY-US",
        "X-EBAY-SOA-SERVICE-NAME: 1.0.0");
	

    $ch = curl_init();
    $res = curl_setopt($ch, CURLOPT_URL, $eBayAPIURL);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_setopt($ch, CURLOPT_HEADER, 0); // 0 = Don't give me the return header 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $ebayapiheader); // Set this for eBayAPI 
    curl_setopt($ch, CURLOPT_POST, 1); // POST Method 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data); //My XML Request 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
	    $body = curl_exec($ch); //Send the request 
    curl_close($ch); // Close the connection
    return $body;
}

//for scrapping from ebay function
function get_html_from_url($url)
{
    $useragent = $USER_AGENT_LIST[random_int(0,50)];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
    $output = curl_exec($ch);
    curl_close($ch);
    if(!empty($output)){
        $pokemon_doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $pokemon_doc->loadHTML($output);
        libxml_clear_errors();
        $pokemon_xpath = new DOMXPath($pokemon_doc);
        return $pokemon_xpath;

    }
    else{
        return;
    }
}
function get_items_transactions($items,$period,$page_no) {
 
	global $APPNAME;
	global $responseEncoding;
    global $eBayAPIURL;
    global $eBayAPIURL_finding;
    global $COMPATIBILITYLEVEL;
    global $DEVNAME;
    global $APPNAME;
    global $CERTNAME;
    global $AUTH_TOKEN;
    global $eBayAPIURL_trading;

    $curl = array();
    $result = array();
    if (!$items) { return; }
    $now_date = date("Y-m-d H:i:s");
    $trans_date_start = date("Y-m-d H:i:s", strtotime("$now_date -$period day"));
    $item_trans_extra_info = array();
    //$seller_feedback = 0;
    if ($mh1 = curl_multi_init()) {
        $item_trans_item_ids = array();
        foreach ($items as $item_id) {
            $item_id = (string)$item_id;
            //var_dump($item_id);

            if ($curl[$item_id] = curl_init()) {

                $html_request_head = array("X-EBAY-API-SITEID:0",
                                "X-EBAY-API-COMPATIBILITY-LEVEL:" . $COMPATIBILITYLEVEL,
                                "X-EBAY-API-CALL-NAME:" . "GetItemTransactions",
                                "X-EBAY-API-APP-NAME:" . $APPNAME,
                                "X-EBAY-API-DEV-NAME:" . $DEVNAME,
                                "X-EBAY-API-CERT-NAME:" . $CERTNAME);

                $html_request_body = '<?xml version="1.0" encoding="utf-8"?>
                <GetItemTransactionsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
                    <RequesterCredentials>
                        <eBayAuthToken>' . $AUTH_TOKEN . '</eBayAuthToken>
                    </RequesterCredentials>
                    <ErrorLanguage>en_US</ErrorLanguage>
                    <WarningLevel>High</WarningLevel>
                    <ItemID>' . $item_id . '</ItemID>
                    <NumberOfDays>' . $period . '</NumberOfDays>
                    <Pagination>
                        <EntriesPerPage>200</EntriesPerPage>
                        <PageNumber>' . $page_no . '</PageNumber>
                    </Pagination>
                    <OutputSelector>ItemID</OutputSelector>
                    <OutputSelector>ConvertedTransactionPrice</OutputSelector>
                    <OutputSelector>CreatedDate</OutputSelector>
                    <OutputSelector>QuantityPurchased</OutputSelector>
                    <OutputSelector>TransactionPrice</OutputSelector>
                    <OutputSelector>PaginationResult</OutputSelector>
                    <OutputSelector>FeedBackScore</OutputSelector>
                </GetItemTransactionsRequest>';
                if ($curl[$item_id] = curl_init()) {
                    curl_setopt($curl[$item_id], CURLOPT_URL, $eBayAPIURL_trading);
                    curl_setopt($curl[$item_id], CURLOPT_HEADER, 1);
                    curl_setopt($curl[$item_id], CURLOPT_HTTPHEADER, $html_request_head);
                    curl_setopt($curl[$item_id], CURLOPT_POSTFIELDS, $html_request_body);
                    curl_setopt($curl[$item_id], CURLOPT_TIMEOUT, 120);
                    curl_setopt($curl[$item_id], CURLOPT_RETURNTRANSFER, 1);
    
                    curl_setopt($curl[$item_id], CURLOPT_FOLLOWLOCATION, 1);
                    curl_multi_add_handle($mh1, $curl[$item_id]);
                    $item_trans_item_ids[(int)$curl[$item_id]] = $item_id;
                }
            }
        }
        $running = null;
        try {
            // run your code here
            do {       
                while (($status = curl_multi_exec($mh1, $running)) == CURLM_CALL_MULTI_PERFORM);
                if ($status != CURLM_OK) break;
                while ($info = curl_multi_info_read($mh1)) {
                    $handle = $info['handle'];
                    $item_trans_data = curl_multi_getcontent($handle);
                    
                    if ($item_trans_data) {
                        
                        $item_id = $item_trans_item_ids[(int)$handle];
                        if (strpos($item_trans_data, "<ShortMessage>Call usage limit has been reached.</ShortMessage>")) {
                            $item_trans_extra_info[$item_id]['page_count'] = -1;
                        } else {

                            $item_trans_xml = simplexml_load_string(strstr($item_trans_data, '<?xml'));
                            
                            if (gettype($item_trans_xml) == 'object') {
                                $result[$item_id] = array();
                                if (!isset($item_trans_xml->Errors) || empty(($item_trans_xml->Errors))) {
                                    $item_trans_extra_info[$item_id]['page_count'] = $item_trans_xml->PaginationResult[0]->TotalNumberOfPages;
                                    //if (isset($item_trans_xml->Item[0]->Seller) && isset($item_trans_xml->Item[0]->Seller[0]->FeedbackScore) && $seller_feedback == 0) {
                                    //    $seller_feedback = $item_trans_xml->Item[0]->Seller[0]->FeedbackScore;
                                    //}
                                    if (isset($item_trans_xml->TransactionArray)) {
                                        foreach ($item_trans_xml->TransactionArray[0]->children() as $trans) {
                                            
                                            $trans_date = date("Y-m-d H:i:s", strtotime($trans[0]->CreatedDate));
                                            if($trans_date >= $trans_date_start){
                                                $quantity = (int)$trans[0]->QuantityPurchased;                            
                                                
                                                $converted_price = (float)$trans[0]->ConvertedTransactionPrice;
                                                $converted_currency = utf8_decode($trans[0]->ConvertedTransactionPrice['currencyID']);
                                                $trans_price = (float)$trans[0]->TransactionPrice;
                                                $trans_currency = utf8_decode($trans[0]->TransactionPrice['currencyID']);
                                                //$buyer_id = $trans[0]->Buyer[0]->UserID;
                                                //$shipping_country = $trans[0]->Buyer[0]->BuyerInfo[0]->ShippingAddress[0]->Country;
                                                //$shipping_postal = $trans[0]->Buyer[0]->BuyerInfo[0]->ShippingAddress[0]->PostalCode;
                                                $item_trans_extra_info[$item_id]['dirty_price'] = $trans_price;
                                                $r = array(
                                                    'item_id' => $item_id,
                                                    //'seller_id' => $items[$item_id]['seller_id'],
                                                    'quantity' => $quantity,
                                                    'trans_date' => $trans_date,
                                                    'converted_price' => $converted_price,
                                                    'converted_currency' => $converted_currency,
                                                    'trans_price' => $trans_price,
                                                    'trans_currency' => $trans_currency,
                                                    'total_amount' => (float)$converted_price * $quantity
                                                    //'buyer_id' => $buyer_id,
                                                    //'shipping_country' => $shipping_country,
                                                    //'shipping_postal' => $shipping_postal,
                                                );
                                                array_push($result[$item_id],$r);
                                            }
                                            
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($running && curl_multi_select($mh1) === -1) usleep($EBAY_CURL_MULTI_SLEEP);
                        curl_multi_remove_handle($mh1, $handle);
                        //curl_close($handle);
                }
            }while($running);
        }
        catch (exception $e) {
            //code to handle the exception
            curl_multi_close($mh1);
            return array("status"=>"error","error_msg"=>"Something went wrong.Please retry again.");
        }
        curl_multi_close($mh1);
    }
    $res = array();
    foreach($items as $item_id){
        $total_amount = 0.0;
        $total_quantity = 0;
        
        foreach($result[(string)$item_id] as $it){
            $total_amount += $it['total_amount'];
            $total_quantity += $it['quantity'];
        }
        $res[(string)$item_id] = array("total_amount" => $total_amount,"total_quantity"=>$total_quantity);
    }
    //var_dump($res);
    return array("status"=>"success","result"=>$res);
    
}
function get_seller_sold_items($seller_name,$period) {
 
	global $APPNAME;
	global $responseEncoding;
    global $eBayAPIURL;
    global $eBayAPIURL_finding;
    global $SOLD_PERIOD;
    $apiEndPoint = '';
    $apicalla  = "https://svcs.ebay.com/services/search/FindingService/v1?";
    $apicalla  = $eBayAPIURL_finding;
    $apicalla .= "OPERATION-NAME=findCompletedItems";
    $apicalla .= "&SERVICE-VERSION=1.7.0";
	#$apicalla .= "&GLOBAL-ID=$countryID";
    $apicalla .= "&SECURITY-APPNAME=$APPNAME";
    $apicalla .= "&RESPONSE-DATA-FORMAT=$responseEncoding";
    $apicalla .= "&REST-PAYLOAD";
    
    if(strlen($seller_name) > 0){
        $apicalla .= "&itemFilter(0).name=Seller";
        $apicalla .= "&itemFilter(0).value=$seller_name";
        $apicalla .= "&itemFilter(1).name=HideDuplicateItems";
        $apicalla .= "&itemFilter(1).value=True";
        $apicalla .= "&itemFilter(2).name=EndTimeFrom";
        #calc 30 days ago datetime
        $endtimefrom = date_modify(date_create(),'-30 day');//2 day is business day
        
        #$endtimefrom = gmdate(DATE_ISO8601,$endtimefrom->getTimestamp());
        $endtimefrom = $endtimefrom->format("Y-m-d\TH:i:s");
        
        $apicalla .= "&itemFilter(2).value=$endtimefrom";
    }
    else{
        $result= array();
        $result['status'] = 'error';
        $result['error_msg'] = "Must need seller name.";
        return json_encode($result);
    }
    $apicalla .= "&paginationInput.entriesPerPage=100";
    $apicalla .= "&outputSelector=PictureURLLarge";
	#$apicalla .= "&sortOrder=WatchCountDecreaseSort";
	
    // Load the call and capture the document returned by eBay API
    #echo $apicalla;exit;

    libxml_use_internal_errors(true);
    $resp = simplexml_load_file($apicalla);
    #echo print_r($resp);exit;
    

    $retna = '';
    #$sold_counts = get_items_sold_count($catID, "http://open.api.ebay.com/Shopping?", $kw, $countryID,$itemIDs);
    // Verify whether call was successful
    if ($resp->ack == "Success") {

      $total_entries = $resp->searchResult->paginationOutput->totalEntries;
      $total_pages = $resp->searchResult->paginationOutput->totalPages;
      // For each item node, build a table cell and append it to $retna
       
      //$first_page_xpath = get_html_from_url($resp->searchResult->item[0]->viewItemURL);
      //find purchase url
      //var_dump($resp->searchResult->item[0]->viewItemURL);
      
      /*$href = $first_page_xpath->query('//a[contains(@class,"vi-txt-underline")]');
      
      if($href->length > 0){
        $href_url=$href[0]->getAttribute("href");
        list($pur_prefix_url,$item_preg,$trksid) = explode('&',$href_url);
      }*/
      //else
      //{
      //  return "No HREF";
      //}

      //$pur_page_xpath = get_html_from_url($href_url);
      //var_dump($href_url);
      /*$pur_page_xpath = get_html_from_url($href_url);
      $pur_admin_table = $pur_page_xpath->query('//div[@class="BHbidSecBorderGrey"][1]/div/table[@cellpadding="5"]/tr/td');
      
      $special_offers=array('Sold as a special offer','Accepted','Best offer');
      $i = 0;
      $prices=array();
      $amounts=array();
      $dates=array();
      $sum_money = 0.0;
      $sum_amount = 0;
      foreach($pur_admin_table as $pur_history_item){
          //var_dump($pur_history_item->nodeValue);
          $i ++;
          //price
          if($i % 6 == 3){
            array_push($prices,$pur_history_item->nodeValue);
            if(in_array($pur_history_item->nodeValue,$special_offers)){
                $sum_money += 10.0;
            }
            else{
                $x = explode(' ',$pur_history_item->nodeValue);
                var_dump($x[1]);
                if($x[0] == 'US'){
                    $y = explode('$',$x[1]);
                    $sum_money += floatval($y[1]);
                }
            }
          }
          //sold count
          elseif($i % 6 == 4)
          {
            array_push($amounts,$pur_history_item->nodeValue);
            $sum_amount += intval($pur_history_item->nodeValue);
          }
          //date
          elseif($i % 6 == 5)
          {
            array_push($dates,$pur_history_item->nodeValue);
          }
      }*/
      $sold_items_ids = array();
      foreach($resp->searchResult->item as $item) {
        array_push($sold_items_ids,$item->itemId);
      }
      $sss = get_items_transactions($sold_items_ids,(int)$period,1);
      if($sss['status']=='success'){$sold_infos=$sss['result'];}
      if($sss['status']=='error'){return json_encode($sss);}
      //$seller_feedback = $res['seller_feedback'];
      $listings  = 0;
      $saleearnings  = 0.0;
      $solditems = 0;
      foreach($resp->searchResult->item as $item) {
        //Scrapping for sales purchase history
        //var_dump($href_url);
        //$href_url = $pur_prefix_url.'&item='.$item->itemId.'&'.$trksid;
        //$href_url = "https://offer.ebay.com/ws/eBayISAPI.dll?ViewBidsLogin&item=".$item->itemId."&rt=nc&_trksid=p2047675.l2564";
        //var_dump($href_url);
        //sleep(3);
        //$pur_page_xpath = get_html_from_url($href_url);
        #$pur_admin_table = $pur_page_xpath->query('//div[@class="BHbidSecBorderGrey"][1]/div/table[@cellpadding="5"]/tr/td');
        
        /*var_dump($href_url);exit;
        $pur_admin_table_header = $pur_page_xpath->query('//table[@cellpadding="5"]/tr/th');
        $s = 0;
        while($pur_admin_table_header->length == 0){
            $s++;
            var_dump($s);
            $pur_page_xpath = get_html_from_url($href_url);
            $pur_admin_table_header = $pur_page_xpath->query('//table[@cellpadding="5"]/tr/th');
        }
        var_dump($s);exit;
        $field_count = $pur_admin_table_header->count();
        $pur_admin_table = $pur_page_xpath->query('//table[@cellpadding="5"]/tr/td');

        $special_offers=array('Sold as a special offer','Accepted','Expired');
        $i = 0;
        $prices=array();
        $amounts=array();
        $dates=array();
        $sum_money = 0.0;
        $sum_amount = 0;
        foreach($pur_admin_table as $pur_history_item){
            //var_dump($pur_history_item->nodeValue);
            $i ++;
            //price
            
            if($i % $field_count == $field_count - 3){
                array_push($prices,$pur_history_item->nodeValue);
                if(in_array($pur_history_item->nodeValue,$special_offers)){
                    $sum_money += 10.0;
                }
                else{
                    $x = explode(' ',$pur_history_item->nodeValue);
                    if($x[0] == 'US'){
                        $y = explode('$',$x[1]);
                        $sum_money += floatval($y[1]);
                    }
                }
            }
            //sold count
            elseif($i % $field_count == $field_count - 2)
            {
                
                array_push($amounts,$pur_history_item->nodeValue);
                $sum_amount += intval($pur_history_item->nodeValue);
                
            }
            //date
            elseif($i % $field_count == $field_count - 1)
            {
                array_push($dates,$pur_history_item->nodeValue);
            }
        }
        //get html content from the site.
        
        $scrap_url = $item->viewItemURL;*/
        
        //$sold_infos = get_items_transactions(array($item->itemId),$SOLD_PERIOD,1);
        if($sold_infos[(string)$item->itemId]['total_amount'] == 0.0){
            continue;
        }
        $listings++;
        $saleearnings += $sold_infos[(string)$item->itemId]['total_amount'];
        $solditems += $sold_infos[(string)$item->itemId]['total_quantity'];
        // Determine which price to display
        if ($item->sellingStatus->currentPrice) {
			$price = $item->sellingStatus->currentPrice;
        } else {
			$price = $item->sellingStatus->convertedCurrentPrice;
        }
		
		$img = '';
		$str_count = isset($sold_counts[(string)$item->itemId])? $sold_counts[(string)$item->itemId]:' ';
		if ($item->pictureURLLarge) {
			$img = $item->pictureURLLarge;
        } else {
			$img = $item->galleryURL;
        }
		
		#if($item->listingInfo->watchCount > 999){
		#	$hot = '<i style="margin-left:10px; font-size:40px; color:red;" class="fab fa-gripfire"></i>';

			//$hot = ' <span style="color:yellow;font-weight:bold;background-color:red">(&#10067;)</span>';
		#}
        
        
        
		$retna .= '<tr role="row" height="75px">
            <td class="product-list" style="width: 40%; list-style: none; margin: 0; padding: 0">
            <div class="product-img" style="float: left" onclick="return show_item_wind(\''.$item->viewItemURL.'\');" onmouseover="show_product_img(' . $item->itemId . ')" onmouseleave="hide_product_img(' . $item->itemId . ')">
                <img style="border: 1px solid; border-color: #c5c5c5; width: 55px; height: 55px" src=' . $img . '>
            </div>
                <img id=' . "product-large-img-" . $item->itemId . ' src="' . $img . '" style="display: None; position: absolute; border: 3px solid; margin-left: 60px; border-color: #3c8dbc; width: 20%; z-index: 999">
            <div div class="product-info" style="margin-left: 60px">' . $item->title . '</div>
            </td>
            <td class="text-truncate">' . $price . '</td>
            <td class="text-truncate">' . $sold_infos[(string)$item->itemId]['total_quantity'] . '</td>
            <td class="text-truncate">' . $sold_infos[(string)$item->itemId]['total_amount'] . '</td>
            <td class="text-truncate">' . $item->primaryCategory->categoryName . '</td>
            <td class="text-truncate">' . date("Y-m-d", strtotime($item->listingInfo->startTime)) . '</td>
        </tr>';
    }
    /*$retna = '<div class="" id="search_result_card">
        <div class="card">
            <div class="card-header">
                <div class="card-title-wrap bar-warning">
                    <h4 class="card-title">Search Results</h4>
                </div>
            </div>
            <div class="card-body">
                <div class="card-block">
                    <div class="table-responsive">
                        <table id="recent-orders" class="table table-hover table-xl mb-0 table-striped table-bordered dom-jQuery-events">
                            <thead>
                                <tr>
                                    <th class="border-top-0">Image</th>                                
                                    <th class="border-top-0">Product Name</th>
                                    <th class="border-top-0">Uploaded Date</th>
                                    <th class="border-top-0">Category</th>
                                    <th class="border-top-0">Price</th>
                                    <th class="border-top-0">Sales</th>
                                    <th class="border-top-0">Sold Amount</th>
                                </tr>
                            </thead>
                            <tbody>'.$retna.'
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>';*/
        
      } else {
          // If there were errors, print an error
            $result= array();
            $result['status'] = "error";
            $result['error_msg'] = "Seller Name is not correct or Network Problem.Please check.";
            return json_encode($result);
          
    }  // 
    if($listings != 0){
        $result['avgprice'] = sprintf('%0.2f $', $saleearnings/$solditems);
        $result['saleearnings'] = sprintf('%0.2f $', $saleearnings);
    }
    else{
        $result['avgprice'] = '';
        $result['saleearnings'] = '0.0 $';
    }
    $result['status'] ="success";
    $result['solditems'] = $solditems;
    $result['data_table'] = $retna;
    $result['listings'] = $listings;
    return json_encode($result);
}




function get_item_transactions($item_id,$period,$page_no) {
 
	global $APPNAME;
	global $responseEncoding;
    global $eBayAPIURL;
    global $eBayAPIURL_finding;
    global $COMPATIBILITYLEVEL;
    global $DEVNAME;
    global $APPNAME;
    global $CERTNAME;
    global $AUTH_TOKEN;
    global $eBayAPIURL_trading;

    if (!$item_id) { return; }

    $html_request_head = array("X-EBAY-API-SITEID:0",
                    "X-EBAY-API-COMPATIBILITY-LEVEL:" . $COMPATIBILITYLEVEL,
                    "X-EBAY-API-CALL-NAME:" . "GetItemTransactions",
                    "X-EBAY-API-APP-NAME:" . $APPNAME,
                    "X-EBAY-API-DEV-NAME:" . $DEVNAME,
                    "X-EBAY-API-CERT-NAME:" . $CERTNAME);

    $html_request_body = '<?xml version="1.0" encoding="utf-8"?>
    <GetItemTransactionsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
        <RequesterCredentials>
            <eBayAuthToken>' . $AUTH_TOKEN . '</eBayAuthToken>
        </RequesterCredentials>
        <ErrorLanguage>en_US</ErrorLanguage>
        <WarningLevel>High</WarningLevel>
        <ItemID>' . $item_id . '</ItemID>
        <NumberOfDays>' . $period . '</NumberOfDays>
        <Pagination>
            <EntriesPerPage>200</EntriesPerPage>
            <PageNumber>' . $page_no . '</PageNumber>
        </Pagination>
        <OutputSelector>ItemID</OutputSelector>
        <OutputSelector>ConvertedTransactionPrice</OutputSelector>
        <OutputSelector>CreatedDate</OutputSelector>
        <OutputSelector>QuantityPurchased</OutputSelector>
        <OutputSelector>TransactionPrice</OutputSelector>
        <OutputSelector>PaginationResult</OutputSelector>
    </GetItemTransactionsRequest>';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $eBayAPIURL_trading);
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $html_request_head);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $html_request_body);
    curl_setopt($curl, CURLOPT_TIMEOUT, 120);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    $item_trans_data = curl_exec($curl);
    if (strpos($item_trans_data, "<ShortMessage>Call usage limit has been reached.</ShortMessage>")) {
        return "Call API Limited";
    }
    $item_trans_infos = array();
    $item_trans_xml = simplexml_load_string(strstr($item_trans_data, '<?xml'));
    if (gettype($item_trans_xml) == 'object') {
        if (!isset($item_trans_xml->Errors) || empty(($item_trans_xml->Errors))) {
            $page_count = $item_trans_xml->PaginationResult[0]->TotalNumberOfPages;
            if (isset($item_trans_xml->TransactionArray)) {
                foreach ($item_trans_xml->TransactionArray[0]->children() as $trans) {
                    $quantity = (int)$trans[0]->QuantityPurchased;                            
                    $trans_date = date("Y-m-d H:i:s", strtotime($trans[0]->CreatedDate));
                    $converted_price = (float)$trans[0]->ConvertedTransactionPrice;
                    $converted_currency = utf8_decode($trans[0]->ConvertedTransactionPrice['currencyID']);
                    $trans_price = (float)$trans[0]->TransactionPrice;
                    $trans_currency = utf8_decode($trans[0]->TransactionPrice['currencyID']);
                    //$buyer_id = $trans[0]->Buyer[0]->UserID;
                    //$shipping_country = $trans[0]->Buyer[0]->BuyerInfo[0]->ShippingAddress[0]->Country;
                    //$shipping_postal = $trans[0]->Buyer[0]->BuyerInfo[0]->ShippingAddress[0]->PostalCode;
                    //$item_trans_extra_info[$item_id]['dirty_price'] = $trans_price;
                    array_push($item_trans_infos,array(
                        //'item_id' => $item_id,
                        //'seller_id' => $items[$item_id]['seller_id'],
                        'quantity' => $quantity,
                        'trans_date' => $trans_date,
                        'converted_price' => $converted_price,
                        'converted_currency' => $converted_currency,
                        'trans_price' => $trans_price,
                        'trans_currency' => $trans_currency,
                        'total_amount' => (float)$converted_price * $quantity,
                        //'buyer_id' => $buyer_id,
                        //'shipping_country' => $shipping_country,
                        //'shipping_postal' => $shipping_postal,
                    ));
                }
            }
        }
    }
    $total_amount = 0.0;
    $total_quantity = 0;
    foreach($item_trans_infos as $trans_){
        $total_amount += $trans_['total_amount'];
        $total_quantity += $trans_['quantity'];
    }
    return array("total_amount" => $total_amount,"total_quantity" => $total_quantity);
}

?>