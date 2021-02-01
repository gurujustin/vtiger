<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Save_Action extends Vtiger_Save_Action {

	public function process(Vtiger_Request $request) {
		//echo "<pre>"; print_r($request); exit;
		
		if($user->id == 5){
		
		    $curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://api.europrime.com/consumer/login",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "{\n  \"username\": \"abraaj\",\n  \"password\": \"qI9PA10wisq2\"\n}",
			  CURLOPT_HTTPHEADER => array(
				"content-type: application/json",
				"user-agent: api user/v1.0.0"
			  ),
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			
			curl_close($curl);
			
			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  $token = json_decode($response);
			}
				
			$curl = curl_init();
			
			//echo "<pre>"; print_r($request); exit;
			
			$str = explode(" ",$request->get('lastname'));
			$phone = $request->get('phone');
			$mailingcountry = $request->get('mailingcountry');
			$ip = $request->get('cf_856');
			$age = $request->get('cf_854');
            $email = $request->get('email');
			
			curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.europrime.com/v3/accounts",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "{\n\"firstName\":\"$str[0]\",\n\"lastName\":\"$str[1]\",\n\"email\":\"$email\",\n\"phone\":\"$phone\",\n\"password\":\"qI9PA10wisq2\",\n\"country\":\"ZA\",\n\"language\":\"en\",\n\"leadSource\":\"LP\",\n\"ip\":\"$ip\",\n\"funnel_id\":\"fid-1234-5678-9123-4567\",\n\"d1\":\"d1\",\n\"d2\":\"$age\",\n\"campaign_id\":\"campaign_id\",\n\"campaign_name\":\"ABRAAJ\",\n\"sub_campaign_id\":\"sub_campaign_id\",\n\"sub_campaign_name\":\"sub_campaign_name\"\n}",
			CURLOPT_HTTPHEADER => array(
			"authorization: Bearer ".$token->token,
			"content-type: application/json",
			"user-agent: api user/v1.0.0"
			),
			));
			
			$response = curl_exec($curl);
			$err = curl_error($curl);
			
			curl_close($curl);
			
			if ($err) {
			echo "cURL Error #:" . $err;
			} else {
			echo $response;
			}
		}

		//To stop saveing the value of salutation as '--None--'
		$salutationType = $request->get('salutationtype');
		if ($salutationType === '--None--') {
			$request->set('salutationtype', '');
		}
		parent::process($request);
	}
}
