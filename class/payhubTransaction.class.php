<?php
/* Copyright (C) 2012 PayHub, Inc.
 *
 * See the file "LICENSE" for the full license governing this code.
 * 
 * @author Lon Sun
 * @filename payhubTransaction.class
 * @version 1.2
*/

class payhubTransaction
{
   const API_ROOT = 'https://vtp1.payhub.com/payhubvtws/'; //webservice host
   const API_TRANS_RESOURCE = 'transaction.json';
   const API_CAPAUTH_RESOURCE = 'captureauthtransaction.json';

   public static $raw_response; //always holds the raw response from the most recent transaction request (or null if it has not been used yet)

   protected static $states_map = array("Alabama" => 1,
                                        "Alaska" => 2,
                                        "Arizona" => 3,
                                        "Arkansas" => 4,
                                        "Army America" => 5,
                                        "Army Europe" => 6,
                                        "Army Pacific" => 7,
                                        "California" => 8,
                                        "Colorado" => 9,
                                        "Connecticut" => 10,
                                        "Delaware" => 11,
                                        "Florida" => 12,
                                        "Georgia" => 13,
                                        "Hawaii" => 14,
                                        "Idaho" => 15,
                                        "Illinois" => 16,
                                        "Indiana" => 17,
                                        "Iowa" => 18,
                                        "Kansas" => 19,
                                        "Kentucky" => 20,
                                        "Louisiana" => 21,
                                        "Maine" => 22,
                                        "Maryland" => 23,
                                        "Massachusetts" => 24,
                                        "Michigan" => 25,
                                        "Minnesota" => 26,
                                        "Mississippi" => 27,
                                        "Missouri" => 28,
                                        "Montana" => 29,
                                        "Nebraska" => 30,
                                        "Nevada" => 31,
                                        "New Hampshire" => 32,
                                        "New Jersey" => 33,
                                        "New Mexico" => 34,
                                        "New York" => 35,
                                        "North Carolina" => 36,
                                        "North Dakota" => 37,
                                        "Ohio" => 38,
                                        "Oklahoma" => 39,
                                        "Oregon" => 41,
                                        "Pennsylvania" => 42,
                                        "Rhode Island" => 43,
                                        "South Carolina" => 44,
                                        "South Dakota" => 45,
                                        "Tennessee" => 46,
                                        "Texas" => 47,
                                        "Utah" => 48,
                                        "Vermont" => 49,
                                        "Virginia" => 50,
                                        "Washington" => 51,
                                        "Washington D.C." => 52,
                                        "West Virginia" => 53,
                                        "Wisconsin" => 54,
                                        "Wyoming" => 55,
                                        "AL" => 1,
                                        "AK" => 2,
                                        "AZ" => 3,
                                        "AR" => 4,
                                        "CA" => 8,
                                        "CO" => 9,
                                        "CT" => 10,
                                        "DE" => 11,
                                        "FL" => 12,
                                        "GA" => 13,
                                        "HI" => 14,
                                        "ID" => 15,
                                        "IL" => 16,
                                        "IN" => 17,
                                        "IA" => 18,
                                        "KS" => 19,
                                        "KY" => 20,
                                        "LA" => 21,
                                        "ME" => 22,
                                        "MD" => 23,
                                        "MA" => 24,
                                        "MI" => 25,
                                        "MN" => 26,
                                        "MS" => 27,
                                        "MO" => 28,
                                        "MT" => 29,
                                        "NE" => 30,
                                        "NV" => 31,
                                        "NH" => 32,
                                        "NJ" => 33,
                                        "NM" => 34,
                                        "NY" => 35,
                                        "NC" => 36,
                                        "ND" => 37,
                                        "OH" => 38,
                                        "OK" => 39,
                                        "OR" => 41,
                                        "PA" => 42,
                                        "RI" => 43,
                                        "SC" => 44,
                                        "SD" => 45,
                                        "TN" => 46,
                                        "TX" => 47,
                                        "UT" => 48,
                                        "VT" => 49,
                                        "VA" => 50,
                                        "WA" => 51,
                                        "WV" => 53,
                                        "WI" => 54,
                                        "WY" => 55);

   private static $debug = true; //true/false to enable/disable debug
                                     
   //API authentication credentials
   private static $orgid; //maps to MERCHANT_NUMBER
   private static $username; //maps to USER_NAME
   private static $password; //maps to PASSWORD

   //terminal
   private static $tid; //maps to TERMINAL_NUMBER

   //validation regex for user-supplied input
   protected static $val_orgid = '/^[0-9]{1,8}$/'; //1-8 digits, cannot be empty
   protected static $val_cust_phone = '/^[0-9]{10}$/'; // cannot be empty
   //...

   //all parameters allowed by transaction webservice
   private $all_parameters = array(
   "RECORD_FORMAT",
   "MERCHANT_NUMBER",
   "USER_NAME",
   "PASSWORD",
   "TERMINAL_NUMBER",
   "TRANSACTION_CODE",
   "CARDHOLDER_ID_CODE",
   "CARD_HOLDER_ID_DATA",
   "ACCOUNT_DATA_SOURCE",
   "CUSTOMER_DATA_FIELD",
   "CARD_EXPIRY_DATE",
   "CVV_CODE",
   "CVV_DATA",
   "TRANSACTION_AMOUNT",
   "OFFLINE_APPROVAL_CODE",
   "TRANSACTION_ID",
   "CUSTOMER_ID",
   "CUSTOMER_FIRST_NAME",
   "CUSTOMER_LAST_NAME",
   "CUSTOMER_COMPANY_NAME",
   "CUSTOMER_JOB_TITLE",
   "CUSTOMER_EMAIL_ID",
   "CUSTOMER_WEB",
   "CUSTOMER_PHONE_NUMBER",
   "CUSTOMER_PHONE_EXT",
   "CUSTOMER_PHONE_TYPE",
   "CUSTOMER_BILLING_ADDRESS1",
   "CUSTOMER_BILLING_ADDRESS2",
   "CUSTOMER_BILLING_ADD_CITY",
   "CUSTOMER_BILLING_ADD_STATE",
   "CUSTOMER_BILLING_ADD_ZIP",
   "CUSTOMER_SHIPPING_ADD_NAME",
   "CUSTOMER_SHIPPING_ADDRESS1",
   "CUSTOMER_SHIPPING_ADDRESS2",
   "CUSTOMER_SHIPPING_ADD_CITY",
   "CUSTOMER_SHIPPING_ADD_STATE",
   "CUSTOMER_SHIPPING_ADD_ZIP",
   "TRANSACTION_IS_AUTH");

   //convert amount to internal format (e.g. "1.01" => "101" or "0.5" => "50") - note that this relies on validation to be done first and separately
   protected static function convertAmount($amount) {
      return (int) str_replace('.', '', number_format($amount, 2, '.', ''));
   }

   protected static function getAvsShortResponseText($avs_result_code) {
      switch($avs_result_code)
      {
         case "0":
            return "Not Requested";
            break;
         case "A":
            return "Address Match";
            break;
         case "B":
            return "Address Match";
            break;
         case "C":
            return "Service Unavailable";
            break;
         case "D":
            return "Exact Match";
            break;
         case "F":
            return "Exact Match";
            break;
         case "G":
            return "Verification Unavailable";
            break;
         case "I":
            return "Verification Unavailable";
            break;
         case "M":
            return "Exact Match";
            break;
         case "N":
            return "No Match";
            break;
         case "P":
            return "Zip Match";
            break;
         case "R":
            return "Retry";
            break;
         case "S":
            return "Service Unavailable";
            break;
         case "U":
            return "Verification Unavailable";
            break;
         case "W":
            return "Zip Match";
            break;
         case "X":
            return "Exact Match";
            break;
         case "Y":
            return "Exact Match";
            break;
         case "Z":
            return "Zip Match";
            break;
         case "1":
            return "Cardholder name and zip match";
            break;
         case "2":
            return "Cardholder name, address, and zip match";
            break;
         case "3":
            return "Cardholder name and address match";
            break;
         case "4":
            return "Cardholder name match";
            break;
         case "5":
            return "Cardholder name incorrect, zip match";
            break;
         case "6":
            return "Cardholder name incorrect, address and zip match";
            break;
         case "7":
            return "Cardholder name incorrect, address match";
            break;
         case "8":
            return "Cardholder, all do not match";
            break;
         case "":
            return "";
            break;
         default:
            return "Invalid AVS response code";
      }
   }

   protected static function getAvsLongResponseText($avs_result_code) {
      switch($avs_result_code)
      {
         case "0":
            return "Address verification was not requested.";
            break;
         case "A":
            return "Address match only.";
            break;
         case "B":
            return "Street Address match for international transaction Postal Code not verified because of incompatible formats (Acquirer sent both street address and Postal Code).";
            break;
         case "C":
            return "Street Address and Postal Code not verified for international transaction because of incompatible formats (Acquirer sent both street and Postal Code).";
            break;
         case "D":
            return "Street Address match for international transaction.";
            break;
         case "F":
            return "Street Address and Postal Code Match. Applies to UK only.";
            break;
         case "G":
            return "Non-U.S. Issuer does not participate.";
            break;
         case "I":
            return "Address information not verified for international transaction.";
            break;
         case "M":
            return "Street Address match for international transaction.";
            break;
         case "N":
            return "Address and ZIP Code does not match.";
            break;
         case "P":
            return "Postal Codes match for international transaction Street address not verified because of incompatible formats (Acquirer sent both street address and Postal Code).";
            break;
         case "R":
            return "Issuer system is unavailable.";
            break;
         case "S":
            return "Service is not supported.";
            break;
         case "U":
            return "Address is unavailable.";
            break;
         case "W":
            return "Nine character numeric ZIP match only.";
            break;
         case "X":
            return "Nine character numeric ZIP matchi (only ZIP sent).";
            break;
         case "Y":
            return "Five character numeric ZIP (only ZIP sent).";
            break;
         case "Z":
            return "Five character numeric ZIP match only.";
            break;
         case "1":
            return "Cardholder name and zip match. Amex Only.";
            break;
         case "2":
            return "Cardholder name, address, and zip match. Amex Only.";
            break;
         case "3":
            return "Cardholder name and address match. Amex Only.";
            break;
         case "4":
            return "Cardholder name match. Amex Only.";
            break;
         case "5":
            return "Cardholder name incorrect, zip match. Amex Only.";
            break;
         case "6":
            return "Cardholder name incorrect, address and zip match. Amex Only.";
            break;
         case "7":
            return "Cardholder name incorrect, address match. Amex Only.";
            break;
         case "8":
            return "Cardholder, all do not match. Amex Only.";
            break;
         case "":
            return "";
            break;
         default:
            return "Invalid AVS response code.";
      }
   }

   //get CVV response text from CVV response code
   public static function getCvvResponseText($cvv_response_code) {
      switch($cvv_response_code)
      {
         case "M":
            return "Match";
            break;
         case "N":
            return "No Match";
            break;
         case "P":
            return "Not Processed";
            break;
         case "S":
            return "Not Present (Merchant-indicated)";
            break;
         case "U":
            return "Issuer Not Certified";
            break;
         case "":
            return "Not Requested";
            break;
         default:
            return "Invalid CVV Response Code";
            break;
      }
   }

   //convert the transaction_date_time field to a more readable timestamp
   //expected format is MMDDYY HHMMSS
   //converted to YYYY-MM-DD HH:MM:SS (24-hour time)
   public static function convertTimestamp($timestamp) {
      //validation of the response format from the host is expected to be done elswhere
      if($timestamp)
      {
         return "20".substr($timestamp,4,2)."-".substr($timestamp,0,2)."-".substr($timestamp,2,2)." ".substr($timestamp,7,2).":".substr($timestamp,9,2).":".substr($timestamp,11,2);
      }
      else
      {
         return "";
      }
   }

   //get the phone type code for api
   public static function getPhoneType($phone_type) {
      $phone_type = strtoupper($phone_type);
      
      switch($phone_type) 
      {
         case "HOME":
            return "H";
            break;
         case "WORK":
            return "W";
            break;
         case "MOBILE":
            return "M";
            break;
         default:
            return "Invalid phone type.";
            break;
      }
   }

   //set debug
   public static function setDebug($tf) {
      self::$debug = ($tf) ? true : false;
   }

   //set credentials
   public static function setCredentials($o, $u, $p) {
      //TODO: validate credentials

      self::$orgid = $o;
      self::$username = $u;
      self::$password = $p;
   }

   //set terminal id to use
   public static function setTerminalId($t) {
      self::$tid = $t;
   }

   //function for sending request
   private static function execute($data_to_send, $is_capture_auth = false) {
      $resource = ($is_capture_auth) ? self::API_CAPAUTH_RESOURCE : self::API_TRANS_RESOURCE;

      /** encode data packet **/
      #print_r($data_to_send);
      $data_to_send = json_encode($data_to_send);

      

      if(self::$debug) echo "\nJSON Request Data:\n $data_to_send\n";

      $ch = curl_init();

      if(self::$debug) echo "\ncURL initialized.  Sending request...\n\n";

      

      $c_opts = array(CURLOPT_URL => self::API_ROOT.$resource,
                      CURLOPT_VERBOSE => self::$debug,
                      CURLOPT_SSL_VERIFYHOST => 0,
                      CURLOPT_SSL_VERIFYPEER => false,
                      CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_POST => true,
                      CURLOPT_POSTFIELDS => $data_to_send);

      curl_setopt_array($ch, $c_opts);
      
      $result = curl_exec($ch);
      
      curl_close($ch);
      
      return $result;
   }

   //function for handling response from various methods
   //The response scheme is 0 is success, 1 is conditional success (risk or cis_note) and anything else is fail
   //The response will always include a response text string regardless of success or failure
   //The most recent raw response will always be available via the static property $raw_response
   private static function responseHandler($json_response, $success_code) {
      self::$raw_response = $json_response;

      $response = new stdClass();
      
      $response_obj = json_decode($json_response);
      

      

      //the "clean" response code is either 0 if successful, or whatever error code was received.
      if($response_obj->RESPONSE_CODE == $success_code) $response->result = 0; else $response->result_text = $response_obj->RESPONSE_CODE;

      //most other response fields can be returned as is, just make it easier to understand and access them.
			
			$response->response_code = $response_obj->RESPONSE_CODE;
      $response->result_text = $response_obj->RESPONSE_TEXT;
      $response->transaction_id = $response_obj->TRANSACTION_ID;
      $response->batch_id = $response_obj->BATCH_ID;
      $response->transaction_timestamp = self::convertTimestamp($response_obj->TRANSACTION_DATE_TIME);
      $response->avs_response_code = $response_obj->AVS_RESULT_CODE;
      $response->avs_response_text = self::getAvsShortResponseText($response_obj->AVS_RESULT_CODE);
      $response->cvv_response_code = $response_obj->VERIFICATION_RESULT_CODE;
      $response->cvv_response_text = self::getCvvResponseText($response_obj->VERIFICATION_RESULT_CODE);
      $response->card_token = $response_obj->CARD_TOKEN_NO;
      $response->approval_code = $response_obj->APPROVAL_CODE;
      $response->customer_id = $response_obj->CUSTOMER_ID;

      //update result code if there was a problem with risk or with saving the customer data
      if($response_obj->RISK_STATUS_RESPONSE_CODE != "")
      {
         $response->result = 1;
         $response->result_text .= " (WARNING: $response_obj->RISK_STATUS_RESPONSE_TEXT)";
      }
      else if($response_obj->CIS_NOTE != "")
      {
         $response->result = 1;
         $response->result_text .= " (WARNING: $response_obj->CIS_NOTE)";
      }

      if(self::$debug) 
      {
        echo "\nReponse:\n";
        var_dump($response);
      }

      return $response;
   }

   //set state to internal numeric representation
   protected static function encodeState($state) {
      //this should convert a state full name or abbreviation to the appropriate internal numberic representation
      if(self::$states_map[$state])
      {
         return self::$states_map[$state]; 
      }
      else
      {
         return NULL;
      }
   }

   //make sure the customer web url starts with "http://" - this should be changed at server end soon
   protected static function formatWebUrl($url) {
      if( ! preg_match('/^(http:\/\/|https:\/\/)/', $url)) return "http://".$url; else return $url;
   }

   //the customer phone should be numbers only
   protected static function formatPhone($phone) {
      return preg_replace('/[^0-9]/', '', $phone);
   }

   //clean the card_data field for the raw API.  The start and end sentinels should be removed on any track data before submision.
   protected static function cleanCardData($card_data_type, $card_data) {
      //remove start and end sentinels on customer data field if it is track data
      if($card_data_type == "track1")
      {
         $customer_data_field = preg_replace('/(^%|\?$)/', '', $card_data); //card_data
      }
      else if($card_data_type == "track2")
      {
         $customer_data_field = preg_replace('/(^;|\?$)/', '', $card_data); //card_data
      }
      else
      {
         $customer_data_field = $card_data;
      }

      return $customer_data_field;
   }

   //$user_data = array with data specific to the transaction
   public static function sale($user_data = array()) {
      $request; //outgoing data packet

      //TODO:  add user-supplied parameter validation - probably through validator method

      /** build parameters for transaction request **/

      //set credentials
      $request['MERCHANT_NUMBER'] = self::$orgid;
      $request['USER_NAME'] = self::$username;
      $request['PASSWORD'] = self::$password;
      $request['TERMINAL_NUMBER'] = self::$tid;

      //set payment type (record format and cardholder id code and card holder id data)
      switch($user_data['payment_type'])
      {
         case "credit":
            $request['RECORD_FORMAT'] = "CC";
            $request['CARDHOLDER_ID_CODE'] = "@";
            $request['CARD_HOLDER_ID_DATA'] = "";
            break;
         case "debit":
            $request['RECORD_FORMAT'] = "DD";
            $request['CARD_HOLDER_ID_CODE'] = "K";
            $request['CARD_HOLDER_ID_DATA'] = $user_data['card_pin'];
            break;
         default:
            //do nothing for the moment
      }

      //set transaction code
      $request['TRANSACTION_CODE'] = "01"; //always sale

      //set card data type (account data source)
      switch($user_data['card_data_type'])
      {
         case "track1":
            $account_data_source = "H";
            break;
         case "track2":
            $account_data_source = "D";
            break;
         case "pan":
            $account_data_source = "T";
            break;
         case "token":
            $account_data_source = "Z";
            break;
         default:
            //do nothing for now
      }

      $request['ACCOUNT_DATA_SOURCE'] = $account_data_source;

      //set card number (customer data field)
      $request['CUSTOMER_DATA_FIELD'] = self::cleanCardData($user_data['card_data_type'], $user_data['card_data']);

      //set card expiry date
      $request['CARD_EXPIRY_DATE'] = $user_data['card_exp_month'] . $user_data['card_exp_year'];

      //set amount (transaction amount)
      $request['TRANSACTION_AMOUNT'] = self::convertAmount($user_data['amount']);

      //set note
      $request['TRANSACTION_NOTE'] = $user_data['note'];

      //set card cvv (cvv code and cvv data)
      if(isset($user_data['card_cvv']))
      {
         $request['CVV_CODE'] = ($user_data['card_cvv'] == "") ? "N" : "Y";
         $request['CVV_DATA'] = $user_data['card_cvv'];
      }
      
      //set billing address information
      if(isset($user_data['billing_address1'])) $request['CUSTOMER_BILLING_ADDRESS1'] = $user_data['billing_address1'];
      if(isset($user_data['billing_address2'])) $request['CUSTOMER_BILLING_ADDRESS2'] = $user_data['billing_address2'];
      if(isset($user_data['billing_city'])) $request['CUSTOMER_BILLING_ADD_CITY'] = $user_data['billing_city'];
      if(isset($user_data['billing_state'])) $request['CUSTOMER_BILLING_ADD_STATE'] = self::encodeState($user_data['billing_state']);
      if(isset($user_data['billing_zip'])) $request['CUSTOMER_BILLING_ADD_ZIP'] = $user_data['billing_zip'];

      //set shipping address information
      if(isset($user_data['shipping_address1'])) $request['CUSTOMER_SHIPPING_ADDRESS1'] = $user_data['shipping_address1'];
      if(isset($user_data['shipping_address2'])) $request['CUSTOMER_SHIPPING_ADDRESS2'] = $user_data['shipping_address2'];
      if(isset($user_data['shipping_city'])) $request['CUSTOMER_SHIPPING_ADD_CITY'] = $user_data['shipping_city'];
      if(isset($user_data['shipping_state'])) $request['CUSTOMER_SHIPPING_ADD_STATE'] = self::encodeState($user_data['shipping_state']);
      if(isset($user_data['shipping_zip'])) $request['CUSTOMER_SHIPPING_ADD_ZIP'] = $user_data['shipping_zip'];

      //CIS information
      if(isset($user_data['cust_id'])) $request['CUSTOMER_ID'] = $user_data['cust_id'];
      if(isset($user_data['cust_first_name'])) $request['CUSTOMER_FIRST_NAME'] = $user_data['cust_first_name'];
      if(isset($user_data['cust_last_name'])) $request['CUSTOMER_LAST_NAME'] = $user_data['cust_last_name'];
      if(isset($user_data['cust_phone'])) $request['CUSTOMER_PHONE_NUMBER'] = self::formatPhone($user_data['cust_phone']);
      if(isset($user_data['cust_phone_ext'])) $request['CUSTOMER_PHONE_EXT'] = $user_data['cust_phone_ext'];
      if(isset($user_data['cust_phone_type'])) $request['CUSTOMER_PHONE_TYPE'] = self::getPhoneType($user_data['cust_phone_type']);
      if(isset($user_data['cust_email'])) $request['CUSTOMER_EMAIL_ID'] = $user_data['cust_email'];
      if(isset($user_data['cust_company'])) $request['CUSTOMER_COMPANY_NAME'] = $user_data['cust_company'];
      if(isset($user_data['cust_title'])) $request['CUSTOMER_JOB_TITLE'] = $user_data['cust_title'];
      if(isset($user_data['cust_website'])) $request['CUSTOMER_WEB'] = self::formatWebUrl($user_data['cust_website']);

      /** do request (using cURL) **/

      $response = self::execute($request);

      return self::responseHandler($response, "00");
   }

   //$user_data = array with data specific to the transaction
   public static function void($user_data = array()) {
      $request; //outgoing data packet

      //TODO:  add user-supplied parameter validation - probably through validator method

      /** build parameters for transaction request **/

      //set credentials
      $request['MERCHANT_NUMBER'] = self::$orgid;
      $request['USER_NAME'] = self::$username;
      $request['PASSWORD'] = self::$password;
      $request['TERMINAL_NUMBER'] = self::$tid;

      //set transaction code
      $request['TRANSACTION_CODE'] = "03"; //always void

      //set transaction id
      $request['TRANSACTION_ID'] = $user_data['transaction_id'];

      /** do request (using cURL) **/
      $response = self::execute($request);

      return self::responseHandler($response, "00");
   }

   //$user_data = array with data specific to the transaction
   public static function refund($user_data = array()) {
      $request; //outgoing data packet

      //TODO:  add user-supplied parameter validation - probably through validator method

      /** build parameters for transaction request **/

      //set credentials
      $request['MERCHANT_NUMBER'] = self::$orgid;
      $request['USER_NAME'] = self::$username;
      $request['PASSWORD'] = self::$password;
      $request['TERMINAL_NUMBER'] = self::$tid;

      //set payment type (record format) - always "CC"
      $request['RECORD_FORMAT'] = "CC";

      //set transaction code
      $request['TRANSACTION_CODE'] = "02"; //always refund

      //set transaction id
      $request['TRANSACTION_ID'] = $user_data['transaction_id'];

      /** do request (using cURL) **/
      $response = self::execute($request);

      return self::responseHandler($response, "00");
   }

   //$user_data = array with data specific to the transaction
   public static function offline($user_data = array()) {
      $request; //outgoing data packet

      //TODO:  add user-supplied parameter validation - probably through validator method

      /** build parameters for transaction request **/

      //set credentials
      $request['MERCHANT_NUMBER'] = self::$orgid;
      $request['USER_NAME'] = self::$username;
      $request['PASSWORD'] = self::$password;
      $request['TERMINAL_NUMBER'] = self::$tid;

      //set payment type (record format) - always "CC"
      $request['RECORD_FORMAT'] = "CC";

      //set transaction code
      $request['TRANSACTION_CODE'] = "04"; //always offline

      //set card data type (account data source) - only "pan" or "token" allowed
      switch($user_data['card_data_type'])
      {
         case "pan":
            $account_data_source = "T";
            break;
         case "token":
            $account_data_source = "Z";
            break;
         default:
            //do nothing for now
      }

      $request['ACCOUNT_DATA_SOURCE'] = $account_data_source;

      //set card number (customer data field) - this is always manually entered so no need to "clean"
      $request['CUSTOMER_DATA_FIELD'] = $user_data['card_data'];

      //set card expiry date
      $request['CARD_EXPIRY_DATE'] = $user_data['card_exp_month'] . $user_data['card_exp_year'];

      //set amount (transaction amount)
      $request['TRANSACTION_AMOUNT'] = self::convertAmount($user_data['amount']);

      //set offline approval code
      $request['OFFLINE_APPROVAL_CODE'] = $user_data['offline_approval_code'];

      //set billing address information
      if(isset($user_data['billing_address1']))
      {
         $request['CUSTOMER_BILLING_ADDRESS1'] = $user_data['billing_address1'];
         $request['CUSTOMER_BILLING_ADDRESS2'] = $user_data['billing_address2'];
         $request['CUSTOMER_BILLING_ADD_CITY'] = $user_data['billing_city'];
         $request['CUSTOMER_BILLING_ADD_STATE'] = self::encodeState($user_data['billing_state']);
         $request['CUSTOMER_BILLING_ADD_ZIP'] = $user_data['billing_zip'];
      }

      //set shipping address information
      if(isset($user_data['shipping_address1']))
      {
         $request['CUSTOMER_SHIPPING_ADDRESS1'] = $user_data['shipping_address1'];
         $request['CUSTOMER_SHIPPING_ADDRESS2'] = $user_data['shipping_address2'];
         $request['CUSTOMER_SHIPPING_ADD_CITY'] = $user_data['shipping_city'];
         $request['CUSTOMER_SHIPPING_ADD_STATE'] = self::encodeState($user_data['shipping_state']);
         $request['CUSTOMER_SHIPPING_ADD_ZIP'] = $user_data['shipping_zip'];
      }

      //CIS information
      if(isset($user_data['cust_id'])) $request['CUSTOMER_ID'] = $user_data['cust_id'];
      if(isset($user_data['cust_first_name'])) $request['CUSTOMER_FIRST_NAME'] = $user_data['cust_first_name'];
      if(isset($user_data['cust_last_name'])) $request['CUSTOMER_LAST_NAME'] = $user_data['cust_last_name'];
      if(isset($user_data['cust_phone'])) $request['CUSTOMER_PHONE_NUMBER'] = self::formatPhone($user_data['cust_phone']);
      if(isset($user_data['cust_phone_ext'])) $request['CUSTOMER_PHONE_EXT'] = $user_data['cust_phone_ext'];
      if(isset($user_data['cust_phone_type'])) $request['CUSTOMER_PHONE_TYPE'] = self::getPhoneType($user_data['cust_phone_type']);
      if(isset($user_data['cust_email'])) $request['CUSTOMER_EMAIL_ID'] = $user_data['cust_email'];
      if(isset($user_data['cust_company'])) $request['CUSTOMER_COMPANY_NAME'] = $user_data['cust_company'];
      if(isset($user_data['cust_title'])) $request['CUSTOMER_JOB_TITLE'] = $user_data['cust_title'];
      if(isset($user_data['cust_website'])) $request['CUSTOMER_WEB'] = self::formatWebUrl($user_data['cust_website']);

      /** do request (using cURL) **/
      $response = self::execute($request);

      return self::responseHandler($response, "00");
   }

   //$user_data = array with data specific to the transaction
   public static function authOnly($user_data = array()) {
      $request; //outgoing data packet

      //TODO:  add user-supplied parameter validation - probably through validator method

      /** build parameters for transaction request **/

      //set credentials
      $request['MERCHANT_NUMBER'] = self::$orgid;
      $request['USER_NAME'] = self::$username;
      $request['PASSWORD'] = self::$password;
      $request['TERMINAL_NUMBER'] = self::$tid;

      //set payment type (record format and cardholder id code) - always credit
      $request['RECORD_FORMAT'] = "CC";
      $request['CARDHOLDER_ID_CODE'] = "@";

      //set transaction code
      $request['TRANSACTION_CODE'] = "05"; //always auth only

      //set auth only indicator - always "1" for auth only trans
      $request['TRANSACTION_IS_AUTH'] = "1";

      //set card data type (account data source)
      switch($user_data['card_data_type'])
      {
         case "track1":
            $account_data_source = "H";
            break;
         case "track2":
            $account_data_source = "D";
            break;
         case "pan":
            $account_data_source = "T";
            break;
         case "token":
            $account_data_source = "Z";
            break;
         default:
            //do nothing for now
      }

      $request['ACCOUNT_DATA_SOURCE'] = $account_data_source;

      //set card number (customer data field)
      $request['CUSTOMER_DATA_FIELD'] = self::cleanCardData($user_data['card_data_type'], $user_data['card_data']);

      //set card expiry date
      $request['CARD_EXPIRY_DATE'] = $user_data['card_exp_month'] . $user_data['card_exp_year'];

      //set amount (transaction amount)
      $request['TRANSACTION_AMOUNT'] = self::convertAmount($user_data['amount']);

      //set card cvv (cvv code and cvv data)
      $request['CVV_CODE'] = ($user_data['card_cvv'] == "") ? "N" : "Y";
      $request['CVV_DATA'] = $user_data['card_cvv'];;
      
      //set billing address information
      if(isset($user_data['billing_address1']))
      {
         $request['CUSTOMER_BILLING_ADDRESS1'] = $user_data['billing_address1'];
         $request['CUSTOMER_BILLING_ADDRESS2'] = $user_data['billing_address2'];
         $request['CUSTOMER_BILLING_ADD_CITY'] = $user_data['billing_city'];
         $request['CUSTOMER_BILLING_ADD_STATE'] = self::encodeState($user_data['billing_state']);
         $request['CUSTOMER_BILLING_ADD_ZIP'] = $user_data['billing_zip'];
      }

      //set shipping address information
      if(isset($user_data['shipping_address1']))
      {
         $request['CUSTOMER_SHIPPING_ADDRESS1'] = $user_data['shipping_address1'];
         $request['CUSTOMER_SHIPPING_ADDRESS2'] = $user_data['shipping_address2'];
         $request['CUSTOMER_SHIPPING_ADD_CITY'] = $user_data['shipping_city'];
         $request['CUSTOMER_SHIPPING_ADD_STATE'] = self::encodeState($user_data['shipping_state']);
         $request['CUSTOMER_SHIPPING_ADD_ZIP'] = $user_data['shipping_zip'];
      }

      //CIS information
      if(isset($user_data['cust_id'])) $request['CUSTOMER_ID'] = $user_data['cust_id'];
      if(isset($user_data['cust_first_name'])) $request['CUSTOMER_FIRST_NAME'] = $user_data['cust_first_name'];
      if(isset($user_data['cust_last_name'])) $request['CUSTOMER_LAST_NAME'] = $user_data['cust_last_name'];
      if(isset($user_data['cust_phone'])) $request['CUSTOMER_PHONE_NUMBER'] = self::formatPhone($user_data['cust_phone']);
      if(isset($user_data['cust_phone_ext'])) $request['CUSTOMER_PHONE_EXT'] = $user_data['cust_phone_ext'];
      if(isset($user_data['cust_phone_type'])) $request['CUSTOMER_PHONE_TYPE'] = self::getPhoneType($user_data['cust_phone_type']);
      if(isset($user_data['cust_email'])) $request['CUSTOMER_EMAIL_ID'] = $user_data['cust_email'];
      if(isset($user_data['cust_company'])) $request['CUSTOMER_COMPANY_NAME'] = $user_data['cust_company'];
      if(isset($user_data['cust_title'])) $request['CUSTOMER_JOB_TITLE'] = $user_data['cust_title'];
      if(isset($user_data['cust_website'])) $request['CUSTOMER_WEB'] = self::formatWebUrl($user_data['cust_website']);

      /** do request (using cURL) **/
      $response = self::execute($request);

      return self::responseHandler($response, "00");
   }
   
   //$user_data = array with data specific to the transaction
   public static function captureAuthOnly($user_data = array()) {
      $request; //outgoing data packet

      //TODO:  add user-supplied parameter validation - probably through validator method

      /** build parameters for transaction request **/

      //set credentials
      $request['MERCHANT_NUMBER'] = self::$orgid;
      $request['USER_NAME'] = self::$username;
      $request['PASSWORD'] = self::$password;
      $request['TERMINAL_NUMBER'] = self::$tid;

      //set transaction id
      $request['TRANSACTION_ID'] = $user_data['transaction_id'];

      //set amount (transaction amount)
      $request['TRANSACTION_AMOUNT'] = self::convertAmount($user_data['amount']);

      //set note
      $request['TRANSACTION_NOTE'] = $user_data['note'];

      /** do request (using cURL) **/
      $response = self::execute($request, true);

      return self::responseHandler($response, "4076");
   }
 
   public static function submitBatch($user_data) {
      $request; //outgoing data packet

      //TODO:  add user-supplied parameter validation - probably through validator method

      /** build parameters for transaction request **/

      //set credentials
      $request['MERCHANT_NUMBER'] = self::$orgid;
      $request['USER_NAME'] = self::$username;
      $request['PASSWORD'] = self::$password;
      $request['TERMINAL_NUMBER'] = self::$tid;

      //set transaction code
      $request['TRANSACTION_CODE'] = "00"; //always batch

      //set batch id
      $request['BATCH_ID'] = $user_data['batch_id'];

      /** do request (using cURL) **/
      $response = self::execute($request);

      return self::responseHandler($response, "GB");
   }
}
