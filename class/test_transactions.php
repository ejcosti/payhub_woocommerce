<?php

require("payhubTransaction.class.php");

/* test data */

//credentials
$orgid = '10451';
$username = "Q80qoOF5cf";
$password = "FcO50Qofq8";
$tid = '763';

/*
$orgid = '10166';
$username = "ke8lQ9L6qE";
$password = "QELkql69e8";
$tid = '287';
*/

//cards
$card1_number = "4358809882000478";
$card1_token = "9999000000000156";
$card1_exp_month = "05";
$card1_exp_year = "2020";
$card1_cvv = "770";
$card1_billing_addr1 = "2350 Kerner Blvd";
$card1_billing_addr2 = "";
$card1_billing_city = "San Rafael";
$card1_billing_state = "CA";
$card1_billing_zip = "94901";

$card2_number = "374328632201672";
$card2_token = "9999000000000159";
$card2_exp_month = "06";
$card2_exp_year = "2020";
$card2_cvv = "4992";
$card2_billing_addr1 = "2350 Kerner Blvd";
$card2_billing_addr2 = "";
$card2_billing_city = "San Rafael";
$card2_billing_state = "CA";
$card2_billing_zip = "94901";

$card3_number = "4907980000019611";
$card3_token = "9999000000000066";
$card3_exp_month = "04";
$card3_exp_year = "2014";
$card3_cvv = "614";
$card3_billing_addr1 = "2350 Kerner Blvd";
$card3_billing_addr2 = "";
$card3_billing_city = "San Francisco";
$card3_billing_state = "CA";
$card3_billing_zip = "94901";

$card4_track1 = "%B4907980000019611^PAYHUB INC               /^1404101186970000000000809000000?";
$card4_token = "9999000000000066";
$card4_exp_month = "04";
$card4_exp_year = "2014";
$card4_cvv = "614";
$card4_billing_addr1 = "2350 Kerner Blvd Ste 380";
$card4_billing_addr2 = "";
$card4_billing_city = "San Rafael";
$card4_billing_state = "CA";
$card4_billing_zip = "94901";

$card5_number = "5195170025137261";
$card5_token = "9999000000000407";
$card5_exp_month = "10";
$card5_exp_year = "2015";
$card5_cvv = "656";
$card5_billing_addr1 = "2638 16th Ave";
$card5_billing_addr2 = "";
$card5_billing_city = "San Francisco";
$card5_billing_state = "CA";
$card5_billing_zip = "94116";

//customers
$cust1_first = "Malcolm";
$cust1_last = "Reynolds";
$cust1_phone = "111-111-1111";
$cust1_phone_ext = "123";
$cust1_phone_type = "mobile";
$cust1_email = "mal@serenity.com";
$cust1_company = "Independents Inc";
$cust1_title = "Captain";
$cust1_website = "www.serenity.com";
$cust1_shipping_addr1 = "123 Outer Space";
$cust1_shipping_addr2 = "";
$cust1_shipping_city = "Shadow";
$cust1_shipping_state = "WY";
$cust1_shipping_zip = "12345";

$cust2_first = "Jayne";
$cust2_last = "Cobb";
$cust2_phone = "222-222-1111";
$cust2_phone2 = "222-222-2222";
$cust2_email = "jayne@serenity.com";
$cust2_company = "Independents Inc";
$cust2_title = "Muscle";
$cust2_website = "www.serenity.com";

$cust3_first = "Inara";
$cust3_last = "Serra";
$cust3_phone = "333-333-1111";
$cust3_phone2 = "333-333-2222";
$cust3_email = "inara@serenity.com";
$cust3_company = "Independents Inc";
$cust3_title = "Companion";
$cust3_website = "www.serenity.com";

/* setup test data */

$test = array();
$test_index = 0;

//1
$test_index++;

$test[$test_index]['description'] = "Swipe sale with no customer information.";
$test[$test_index]['method'] = "sale";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "track1",
"card_data" => $card4_track1,
"amount" => ".01",
"note" => "API transaction test #$test_index");

//2
$test_index++;

$test[$test_index]['description'] = "Manual sale with no AVS, CVV, or customer information.";
$test[$test_index]['method'] = "sale";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "pan",
"card_data" => $card2_number,
"card_exp_month" => $card2_exp_month,
"card_exp_year" => $card2_exp_year,
"amount" => ".01",
"note" => "API transaction test #$test_index");

//3
$test_index++;

$test[$test_index]['description'] = "Void the manual sale from previous test.";
$test[$test_index]['method'] = "void";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['transaction_id'] = 'return $response_history[$i-1]["transaction_id"];'; 

//4
$test_index++;

$test[$test_index]['description'] = "Auth Only transaction with no AVS or CVV or customer information.";
$test[$test_index]['method'] = "authOnly";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "pan",
"card_data" => $card1_number,
"card_exp_month" => $card1_exp_month,
"card_exp_year" => $card1_exp_year,
"amount" => ".01",
"note" => "API transaction test #$test_index");

//5
$test_index++;

$test[$test_index]['description'] = "Offline transaction with no AVS or CVV or customer information (fake approval number).";
$test[$test_index]['method'] = "offline";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "pan",
"card_data" => $card2_number,
"card_exp_month" => $card2_exp_month,
"card_exp_year" => $card2_exp_year,
"amount" => ".01",
"offline_approval_code" => "656726",
"note" => "API transaction test #$test_index.");

//6
$test_index++;

$test[$test_index]['description'] = "Submit batch.";
$test[$test_index]['method'] = "submitBatch";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['batch_id'] = 'return $response_history[1]["batch_id"];'; 

//7
$test_index++;

$test[$test_index]['description'] = "Refund a sale from previous batch.";
$test[$test_index]['method'] = "refund";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['transaction_id'] = 'return $response_history[1]["transaction_id"];'; //refund 1st sale from previous batch

//8
$test_index++;

$test[$test_index]['description'] = "Void the refund from previous test.";
$test[$test_index]['method'] = "void";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['transaction_id'] = 'return $response_history[$i-1]["transaction_id"];'; 

//9
$test_index++;

$test[$test_index]['description'] = "Offline with token (fake approval code), no avs or cvv info.";
$test[$test_index]['method'] = "offline";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "token",
"card_data" => $card1_token,
"card_exp_month" => $card1_exp_month,
"card_exp_year" => $card1_exp_year,
"amount" => ".01",
"offline_approval_code" => "123456",
"note" => "API transaction test #$test_index.");

//10
$test_index++;

$test[$test_index]['description'] = "Manual sale with AVS; no CVV or customer information.";
$test[$test_index]['method'] = "sale";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "pan",
"card_data" => $card5_number,
"card_exp_month" => $card5_exp_month,
"card_exp_year" => $card5_exp_year,
"amount" => ".01",
"billing_address1" => $card5_billing_addr1,
"billing_address2" => $card5_billing_addr2,
"billing_city" => $card5_billing_city,
"billing_state" => $card5_billing_state,
"billing_zip" => $card5_billing_zip,
"note" => "API transaction test #$test_index.");

//11
$test_index++;

$test[$test_index]['description'] = "Manual sale using a token with AVS and CVV; no customer information.";
$test[$test_index]['method'] = "sale";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "token",
"card_data" => $card5_token,
"card_exp_month" => $card5_exp_month,
"card_exp_year" => $card5_exp_year,
"amount" => ".01",
"billing_address1" => $card5_billing_addr1,
"billing_address2" => $card5_billing_addr2,
"billing_city" => $card5_billing_city,
"billing_state" => $card5_billing_state,
"billing_zip" => $card5_billing_zip,
"card_cvv" => $card5_cvv,
"note" => "API transaction test #$test_index.");

//12
$test_index++;

$test[$test_index]['description'] = "Submit batch.";
$test[$test_index]['method'] = "submitBatch";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index.");
$test[$test_index]['runtime_data']['batch_id'] = 'return $response_history[$i-1]["batch_id"];'; 

//13
$test_index++;

$test[$test_index]['description'] = "Manual sale with token, AVS, CVV, customer first name, customer last name, customer home phone number, customer company, customer title.";
$test[$test_index]['method'] = "sale";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "token",
"card_data" => $card2_token,
"card_exp_month" => $card2_exp_month,
"card_exp_year" => $card2_exp_year,
"amount" => ".01",
"cust_first_name" => $cust3_first,
"cust_last_name" => $cust3_last,
"cust_phone" => $cust3_phone,
"cust_phone_type" => $cust3_phone_type,
"cust_company" => $cust3_company,
"cust_title" => $cust3_title,
"billing_address1" => $card2_billing_addr1,
"billing_address2" => $card2_billing_addr2,
"billing_city" => $card2_billing_city,
"billing_state" => $card2_billing_state,
"billing_zip" => $card2_billing_zip,
"card_cvv" => $card2_cvv,
"note" => "API transaction test #$test_index.");

//14
$test_index++;

$test[$test_index]['description'] = "Auth only with token, AVS, CVV, customer first name, customer last name, customer email, customer company, customer website address, and shipping address.";
$test[$test_index]['method'] = "authOnly";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "token",
"card_data" => $card1_token,
"card_exp_month" => $card1_exp_month,
"card_exp_year" => $card1_exp_year,
"amount" => ".01",
"cust_first_name" => $cust1_first,
"cust_last_name" => $cust1_last,
"cust_email" => $cust1_email,
"cust_company" => $cust1_company,
"cust_website" => $cust1_website,
"billing_address1" => $card1_billing_addr1,
"billing_address2" => $card1_billing_addr2,
"billing_city" => $card1_billing_city,
"billing_state" => $card1_billing_state,
"billing_zip" => $card1_billing_zip,
"card_cvv" => $card1_cvv,
"shipping_address1" => $cust1_shipping_addr1,
"shipping_address2" => $cust1_shipping_addr2,
"shipping_city" => $cust1_shipping_city,
"shipping_state" => $cust1_shipping_state,
"shipping_zip" => $cust1_shipping_zip,
"note" => "API transaction test #$test_index.");

//15
$test_index++;

$test[$test_index]['description'] = "Capture previous auth only transaction with original amount.";
$test[$test_index]['method'] = "captureAuthOnly";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['transaction_id'] = 'return $response_history[$i-1]["transaction_id"];'; //refund 1st sale from previous batch

//16
$test_index++;

$test[$test_index]['description'] = "Auth only with token, AVS, CVV, customer first name, customer last name, customer company, customer title, and shipping address.";
$test[$test_index]['method'] = "authOnly";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "token",
"card_data" => $card4_token,
"card_exp_month" => $card4_exp_month,
"card_exp_year" => $card4_exp_year,
"amount" => ".01",
"billing_address1" => $card4_billing_addr1,
"billing_address2" => $card4_billing_addr2,
"billing_city" => $card4_billing_city,
"billing_state" => $card4_billing_state,
"billing_zip" => $card4_billing_zip,
"card_cvv" => $card4_cvv,
"cust_first_name" => $cust2_first,
"cust_last_name" => $cust2_last,
"cust_company" => $cust2_company,
"cust_title" => $cust2_title,
"shipping_address1" => $card4_shipping_addr1,
"shipping_address2" => $card4_shipping_addr2,
"shipping_city" => $card4_shipping_city,
"shipping_state" => $card4_shipping_state,
"shipping_zip" => $card4_shipping_zip,
"note" => "API transaction test #$test_index.");

//17
$test_index++;

$test[$test_index]['description'] = "Capture previous auth only transaction with different amount than original.";
$test[$test_index]['method'] = "captureAuthOnly";
$test[$test_index]['data'] = 
array(
"amount" => ".02",
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['transaction_id'] = 'return $response_history[$i-1]["transaction_id"];'; //refund 1st sale from previous batch

//18
$test_index++;

$test[$test_index]['description'] = "Void the captured auth only from previous test.";
$test[$test_index]['method'] = "void";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['transaction_id'] = 'return $response_history[$i-2]["transaction_id"];'; 

//19
$test_index++;

$test[$test_index]['description'] = "Offline with customer first name (fake approval code), customer phone number, customer phone ext, and customer phone type.";
$test[$test_index]['method'] = "offline";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "token",
"card_data" => $card1_token,
"card_exp_month" => $card1_exp_month,
"card_exp_year" => $card1_exp_year,
"amount" => ".01",
"cust_first_name" => $cust1_first,
"cust_last_name" => $cust1_last,
"cust_phone" => $cust1_phone,
"cust_phone_ext" => $cust1_phone_ext,
"cust_phone_type" => $cust1_phone_type,
"offline_approval_code" => "123456",
"note" => "API transaction test #$test_index.");

//20
$test_index++;

$test[$test_index]['description'] = "Void the offline from previous test.";
$test[$test_index]['method'] = "void";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['transaction_id'] = 'return $response_history[$i-1]["transaction_id"];'; 

//21
$test_index++;

$test[$test_index]['description'] = "Manual sale with token, AVS, CVV, and customer ID.";
$test[$test_index]['method'] = "sale";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "token",
"card_data" => $card2_token,
"card_exp_month" => $card2_exp_month,
"card_exp_year" => $card2_exp_year,
"amount" => ".01",
"cust_id" => 654, 
"cust_company" => "cpay", 
"billing_address1" => $card2_billing_addr1,
"billing_state" => $card2_billing_state,
"billing_zip" => $card2_billing_zip,
"card_cvv" => $card2_cvv,
"note" => "API transaction test #$test_index.");

//22
$test_index++;

$test[$test_index]['description'] = "Submit batch.";
$test[$test_index]['method'] = "submitBatch";
$test[$test_index]['data'] = 
array(
"note" => "API transaction test #$test_index."
);
$test[$test_index]['runtime_data']['batch_id'] = 'return $response_history[$i-1]["batch_id"];'; 

//23
$test_index++;

$test[$test_index]['description'] = "Manual sale with token, AVS, CVV, and customer ID - used to test risk response for sales over $1, which should be preset in the VT.";
$test[$test_index]['method'] = "sale";
$test[$test_index]['data'] = 
array(
"payment_type" => "credit",
"card_data_type" => "token",
"card_data" => $card2_token,
"card_exp_month" => $card2_exp_month,
"card_exp_year" => $card2_exp_year,
"amount" => "1.01",
"cust_id" => 654, 
"cust_company" => "cpay", 
"billing_address1" => $card2_billing_addr1,
"billing_state" => $card2_billing_state,
"billing_zip" => $card2_billing_zip,
"card_cvv" => $card2_cvv,
"note" => "API transaction test #$test_index.");

/* run tests */

payhubTransaction::setDebug(true);

//set credentials for all transactions
payhubTransaction::setCredentials($orgid, $username, $password);
payhubTransaction::setTerminalId($tid);


//cycle through all test data and run each test
//for($i=1;$i <= $test_index;$i++)
for($i=23;$i == 23;$i++)
{

   $current_test = $test[$i]; //store object from current test case

   $method = $current_test['method']; //API method to call
   
   //check for runtime data to populate in current test data
   if(isset($current_test['runtime_data']))
   {
      foreach($current_test['runtime_data'] as $k => $v)
      {
         $current_test['data'][$k] = eval($v);
      }
   }

   echo "****\n";
   echo "Test #$i: ".$current_test['description']."\n";
   print_r($current_test['data']);
   echo "==========";
   
   
   $response = payhubTransaction::$method($current_test['data']);
   
   echo "\nRaw response:\n".payhubTransaction::$raw_response."\n";
   print_r($raw_response);
   $response_arr = (array) $response;

   if($response_arr['result'] === 0)
   {
      //store response data for future use
      $response_history[$i] = $response_arr;

      echo "\nPassed!\n\n";
   }
   else
   {
      echo "\nFailed!\n\n";
   }
}

?>
