<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */
// Switch the working directory to base
chdir(dirname(__FILE__) . '/../..');

include_once 'includes/Loader.php';
include_once 'include/Zend/Json.php';
include_once 'vtlib/Vtiger/Module.php';
include_once 'include/utils/VtlibUtils.php';
include_once 'include/Webservices/Create.php';
include_once 'modules/Webforms/model/WebformsModel.php';
include_once 'modules/Webforms/model/WebformsFieldModel.php';
include_once 'include/QueryGenerator/QueryGenerator.php';
include_once 'includes/runtime/EntryPoint.php';
include_once 'includes/main/WebUI.php';
include_once 'include/Webservices/AddRelated.php';


class Webform_Capture {

	function captureNow($request) {
		
		$request['phone'] = $request['countryCode'].$request['phone'];
		$isURLEncodeEnabled = $request['urlencodeenable'];
		$currentLanguage = Vtiger_Language_Handler::getLanguage();
		$moduleLanguageStrings = Vtiger_Language_Handler::getModuleStringsFromFile($currentLanguage);
		vglobal('app_strings', $moduleLanguageStrings['languageStrings']);

		$returnURL = false;
		try {
			if (!vtlib_isModuleActive('Webforms'))
				throw new Exception('webforms is not active');

			$webform = Webforms_Model::retrieveWithPublicId(vtlib_purify($request['publicid']));
			if (empty($webform))
				throw new Exception("Webform not found.");

			$returnURL = $webform->getReturnUrl();
			$roundrobin = $webform->getRoundrobin();

			// Retrieve user information
			$user = CRMEntity::getInstance('Users');
			$user->id = $user->getActiveAdminId();
			$user->retrieve_entity_info($user->id, 'Users');

			// Prepare the parametets
			$parameters = array();
			$webformFields = $webform->getFields();
			foreach ($webformFields as $webformField) {
				if ($webformField->getDefaultValue() != null) {
					$parameters[$webformField->getFieldName()] = decode_html($webformField->getDefaultValue());
				} else {
					//If urlencode is enabled then skipping decoding field names
					if ($isURLEncodeEnabled == 1) {
						$webformNeutralizedField = $webformField->getNeutralizedField();
					} else {
						$webformNeutralizedField = html_entity_decode($webformField->getNeutralizedField(), ENT_COMPAT, "UTF-8");
					}

					if (isset($request[$webformField->getFieldName()])) {
						$webformNeutralizedField = $webformField->getFieldName();
					}
					if (is_array(vtlib_purify($request[$webformNeutralizedField]))) {
						$fieldData = implode(" |##| ", vtlib_purify($request[$webformNeutralizedField]));
					} else {
						$fieldData = vtlib_purify($request[$webformNeutralizedField]);
						$fieldData = decode_html($fieldData);
					}

					$parameters[$webformField->getFieldName()] = stripslashes($fieldData);
				}
				if ($webformField->getRequired()) {
					if (!isset($parameters[$webformField->getFieldName()]))
						throw new Exception("Required fields not filled");
				}
			}

			if ($roundrobin) {
				$ownerId = $webform->getRoundrobinOwnerId();
				$ownerType = vtws_getOwnerType($ownerId);
				$parameters['assigned_user_id'] = vtws_getWebserviceEntityId($ownerType, $ownerId);
			} else {
				$ownerId = $webform->getOwnerId();
				$ownerType = vtws_getOwnerType($ownerId);
				$parameters['assigned_user_id'] = vtws_getWebserviceEntityId($ownerType, $ownerId);
			}

			$moduleModel = Vtiger_Module_Model::getInstance($webform->getTargetModule());
			$fieldInstances = Vtiger_Field_Model::getAllForModule($moduleModel);
			foreach ($fieldInstances as $blockInstance) {
				foreach ($blockInstance as $fieldInstance) {
					$fieldName = $fieldInstance->getName();
					if($fieldInstance->get('uitype') == 56 && $fieldInstance->getDefaultFieldValue() == '') {
						$defaultValue = $request[$fieldName];
					} else if (empty($parameters[$fieldName])) {
						$defaultValue = $fieldInstance->getDefaultFieldValue();
						if ($defaultValue) {
							$parameters[$fieldName] = decode_html($defaultValue);
						}
					} else if ($fieldInstance->get("uitype") == 71 || $fieldInstance->get("uitype") == 72) {
						//ignore comma(,) if it is currency field
						$parameters[$fieldName] = str_replace(",", "", $parameters[$fieldName]);
					}
				}
			} 
		

			// New field added to show Record Source
			$parameters['source'] = 'Webform';

			// Create the record
			$record = vtws_create($webform->getTargetModule(), $parameters, $user);
			$webform->createDocuments($record);
			
			$to = " no-reply@517557.cloudwaysapps.com";
			$subject = "Account Registered!";
			
			$message = '<!DOCTYPE html>
	<html>
	
	<head>
		<title></title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<style type="text/css">
			@media screen {
				@font-face {
					font-family: "Lato";
					font-style: normal;
					font-weight: 400;
					src: local("Lato Regular"), local("Lato-Regular"), url(https://fonts.gstatic.com/s/lato/v11/qIIYRU-oROkIk8vfvxw6QvesZW2xOQ-xsNqO47m55DA.woff) format("woff");
				}
	
				@font-face {
					font-family: "Lato";
					font-style: normal;
					font-weight: 700;
					src: local("Lato Bold"), local("Lato-Bold"), url(https://fonts.gstatic.com/s/lato/v11/qdgUG4U09HnJwhYI-uK18wLUuEpTyoUstqEm5AMlJo4.woff) format("woff");
				}
	
				@font-face {
					font-family: "Lato";
					font-style: italic;
					font-weight: 400;
					src: local("Lato Italic"), local("Lato-Italic"), url(https://fonts.gstatic.com/s/lato/v11/RYyZNoeFgb0l7W3Vu1aSWOvvDin1pK8aKteLpeZ5c0A.woff) format("woff");
				}
	
				@font-face {
					font-family: "Lato";
					font-style: italic;
					font-weight: 700;
					src: local("Lato Bold Italic"), local("Lato-BoldItalic"), url(https://fonts.gstatic.com/s/lato/v11/HkF_qI1x_noxlxhrhMQYELO3LdcAZYWl9Si6vvxL-qU.woff) format("woff");
				}
			}
	
			/* CLIENT-SPECIFIC STYLES */
			body,
			table,
			td,
			a {
				-webkit-text-size-adjust: 100%;
				-ms-text-size-adjust: 100%;
			}
	
			table,
			td {
				mso-table-lspace: 0pt;
				mso-table-rspace: 0pt;
			}
	
			img {
				-ms-interpolation-mode: bicubic;
			}
	
			/* RESET STYLES */
			img {
				border: 0;
				height: auto;
				line-height: 100%;
				outline: none;
				text-decoration: none;
			}
	
			table {
				border-collapse: collapse !important;
			}
	
			body {
				height: 100% !important;
				margin: 0 !important;
				padding: 0 !important;
				width: 100% !important;
			}
	
			/* iOS BLUE LINKS */
			a[x-apple-data-detectors] {
				color: inherit !important;
				text-decoration: none !important;
				font-size: inherit !important;
				font-family: inherit !important;
				font-weight: inherit !important;
				line-height: inherit !important;
			}
	
			/* MOBILE STYLES */
			@media screen and (max-width:600px) {
				h1 {
					font-size: 32px !important;
					line-height: 32px !important;
				}
			}
	
			/* ANDROID CENTER FIX */
			div[style*="margin: 16px 0;"] {
				margin: 0 !important;
			}
		</style>
	</head>
	
	<body style="background-color: #f4f4f4; margin: 0 !important; padding: 0 !important;">
		<!-- HIDDEN PREHEADER TEXT -->
		<div style="display: none; font-size: 1px; color: #fefefe; line-height: 1px; font-family: "Lato", Helvetica, Arial, sans-serif; max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden;"> We\'re thrilled to have you here! Get ready to dive into your new account. </div>
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<!-- LOGO -->
			<tr>
				<td bgcolor="#FFA73B" align="center">
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
						<tr>
							<td align="center" valign="top" style="padding: 40px 10px 40px 10px;"> </td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td bgcolor="#FFA73B" align="center" style="padding: 0px 10px 0px 10px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
						<tr>
							<td bgcolor="#ffffff" align="center" valign="top" style="padding: 40px 20px 20px 20px; border-radius: 4px 4px 0px 0px; color: #111111; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 48px; font-weight: 400; letter-spacing: 4px; line-height: 48px;">
								<h1 style="font-size: 48px; font-weight: 400; margin: 2;">Welcome!</h1> <img src=" https://img.icons8.com/clouds/100/000000/handshake.png" width="125" height="120" style="display: block; border: 0px;" />
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
						<tr>
							<td bgcolor="#ffffff" align="left" style="padding: 20px 30px 40px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
								<p style="margin: 0;">We\'re excited to have you get started. First, you need to confirm your account. Just press the button below.</p>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" align="left">
								<table width="100%" border="0" cellspacing="0" cellpadding="0">
									<tr>
										<td bgcolor="#ffffff" align="center" style="padding: 20px 30px 60px 30px;">
											<table border="0" cellspacing="0" cellpadding="0">
												<tr>
													<td align="center" style="border-radius: 3px;" bgcolor="#FFA73B"><a href="#" target="_blank" style="font-size: 20px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; color: #ffffff; text-decoration: none; padding: 15px 25px; border-radius: 2px; border: 1px solid #FFA73B; display: inline-block;">Confirm Account</a></td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr> <!-- COPY -->
						<tr>
							<td bgcolor="#ffffff" align="left" style="padding: 0px 30px 0px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
								<p style="margin: 0;">If that doesn\'t work, copy and paste the following link in your browser:</p>
							</td>
						</tr> <!-- COPY -->
						<tr>
							<td bgcolor="#ffffff" align="left" style="padding: 20px 30px 20px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
								<p style="margin: 0;"><a href="#" target="_blank" style="color: #FFA73B;">https://bit.li.utlddssdstueincx</a></p>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" align="left" style="padding: 0px 30px 20px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
								<p style="margin: 0;">If you have any questions, just reply to this email—we\'re always happy to help out.</p>
							</td>
						</tr>
						<tr>
							<td bgcolor="#ffffff" align="left" style="padding: 0px 30px 40px 30px; border-radius: 0px 0px 4px 4px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
								<p style="margin: 0;">Cheers,<br>BBB Team</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td bgcolor="#f4f4f4" align="center" style="padding: 30px 10px 0px 10px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
						<tr>
							<td bgcolor="#FFECD1" align="center" style="padding: 30px 30px 30px 30px; border-radius: 4px 4px 4px 4px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 18px; font-weight: 400; line-height: 25px;">
								<h2 style="font-size: 20px; font-weight: 400; color: #111111; margin: 0;">Need more help?</h2>
								<p style="margin: 0;"><a href="#" target="_blank" style="color: #FFA73B;">We&rsquo;re here to help you out</a></p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td bgcolor="#f4f4f4" align="center" style="padding: 0px 10px 0px 10px;">
					<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
						<tr>
							<td bgcolor="#f4f4f4" align="left" style="padding: 0px 30px 30px 30px; color: #666666; font-family: "Lato", Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 400; line-height: 18px;"> <br>
								<p style="margin: 0;">If these emails get annoying, please feel free to <a href="#" target="_blank" style="color: #111111; font-weight: 700;">unsubscribe</a>.</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</body>
	
	</html>';
			
			// Always set content-type when sending HTML email
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			
			// More headers
			$headers .= "From: <".$request['email'].">" . "\r\n";
			$headers .= 'Cc: developer.zeeshan@gmail.com' . "\r\n";
			
			mail($to,$subject,$message,$headers);
			
			if($user->user_name == $request['cf_870']){
	
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
			
			$str = explode(" ",$request['lastname']);
			$phone = $request['phone'];
			$mailingcountry = $request['mailingcountry'];
			$ip = $request['cf_856'];
			$age = $request['cf_854'];

            $email = $request['email'];
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

			$this->sendResponse($returnURL, 'ok');
			return;
		} catch (DuplicateException $e) {
			$sourceModule = $webform->getTargetModule();
			$mailBody = vtranslate('LBL_DUPLICATION_FAILURE_FROM_WEBFORMS', $sourceModule, vtranslate('SINGLE_'.$sourceModule, $sourceModule), $webform->getName(), vtranslate('SINGLE_'.$sourceModule, $sourceModule));

			$userModel = Users_Record_Model::getInstanceFromPreferenceFile($user->id);
			sendMailToUserOnDuplicationPrevention($sourceModule, $parameters, $mailBody, $userModel);

			$this->sendResponse($returnURL, false, $e->getMessage());
			return;
		} catch (Exception $e) {
			$this->sendResponse($returnURL, false, $e->getMessage());
			return;
		}
	}

	protected function sendResponse($url, $success = false, $failure = false) {
		if (empty($url)) {
			if ($success)
				$response = Zend_Json::encode(array('success' => true, 'result' => $success));
			else
				$response = Zend_Json::encode(array('success' => false, 'error' => array('message' => $failure)));

			// Support JSONP
			if (!empty($_REQUEST['callback'])) {
				$callback = vtlib_purify($_REQUEST['callback']);
				echo sprintf("%s(%s)", $callback, $response);
			} else {
				echo $response;
			}
		} else {
			$pos = strpos($url, 'http');
			if ($pos !== false) {
				header(sprintf("Location: %s?%s=%s", $url, ($success ? 'success' : 'error'), ($success ? $success : $failure)));
			} else {
				header(sprintf("Location: http://%s?%s=%s", $url, ($success ? 'success' : 'error'), ($success ? $success : $failure)));
			}
		}
	}

}

// NOTE: Take care of stripping slashes...
$webformCapture = new Webform_Capture();
$request = vtlib_purify($_REQUEST);
$isURLEncodeEnabled = $request['urlencodeenable'];
//Do urldecode conversion only if urlencode is enabled in a form. 
if ($isURLEncodeEnabled == 1) {
	$requestParameters = array();
	// Decoding the form element name attributes.
	foreach ($request as $key => $value) {
		$requestParameters[urldecode($key)] = $value;
	}
	//Replacing space with underscore to make request parameters equal to webform fields
	$neutralizedParameters = array();
	foreach ($requestParameters as $key => $value) {
		$modifiedKey = str_replace(" ", "_", $key);
		$neutralizedParameters[$modifiedKey] = $value;
	}
	$webformCapture->captureNow($neutralizedParameters);
} else {
	$webformCapture->captureNow($request);
}
?>