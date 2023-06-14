<?php
namespace App\Modules; 


 
class SMSBongoLiveModule{


	public function __construct(){
		
	}

	//return from php version -- json version via curl
	public function bongolive_response_code_defination($code){
        switch ($code) {
            case '-1': return "Invalid XML Format"; break;
            case '-2': return "Not enough credits in account"; break;
            case '-3': return "Invalid API key"; break;
            case '-4': return "Destination Mobile number missing / Invalid format"; break;
            case '-5': return "SMS text missing"; break;
            case '-6': return "Sender name missing / invalid format / Not active in account"; break;
            case '-7': return "Network Not Covered"; break;
            case '-8': return "Error – Undefined"; break;
            case '-19': return "Invalid message id, too long (max 36 chars) or contains non numeric character"; break;
            case '-10': return "Maximum number of recipient in one single API call is 100"; break;
            case '-11': return "Error – Undefined"; break;
            case '-12': return "Message too long (max 480 characters)"; break;
            case '-13': return "Invalid Username / Password"; break;
            case '-14': return "Invalid send time"; break;
            case '0': return "Successful - Placed in queue for delivery "; break;
            case 0: return "Successful - Placed in queue for delivery "; break;
            case 1: return "Successful - Msg Sent"; break;
            default:return "Successful - Msg Sent";
                break;
        }
    }


    /*
		SEND A SINGLE SMS (NON-XML FORMAT)
    */
	public function send_sms($config_model,$receiver_phone,$sms_body,$_sender_name = "INFO"){
		
		try{
			ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
	        set_time_limit(0); //to prevent the script from dying
	        $this->accountModel = $config_model;
	        //echo $this->accountModel->customer_code;
	        echo "\n-- SMS Module -- \n";

	        //======================REQUIRED INFORMATION ============================
	        
	        date_default_timezone_set('Africa/Nairobi');
	        $sendername = ($_sender_name == "INFO" || $_sender_name == "info" || $_sender_name == null) ? $this->accountModel->api_sender_name : $_sender_name;//
	        $username = $this->accountModel->api_account_id;
	        $password = $this->accountModel->api_secrete_password;;
	        $apikey = $this->accountModel->api_key;
	        $apiSecretekey = $this->accountModel->api_token;
	       
	        $senddate = "";

	        echo "\n-> ". $this->accountModel->api_sender_name." : ". $receiver_phone."\n";
			
	        ///////////////////////////////////////////////////////////////////////////
			//.... replace <api_key> and <secrete_key> with the valid keys obtained from the platform, under profile>authentication information
			$api_key='e80ba44a4de10ff9';//$apikey;
			$secret_key = 'YzU2ZDRiZWNhZWVkMWI4NWJmYjYzNWJlNjBiNjJmZjdkNGUxMzk3ZTViYTYwYzExNTE4NWI1ZDQyM2QyZDkwZA==';//$apiSecretekey;
			// The data to send to the API
			$postData = array(
			    'source_addr' =>"VFD Plus",//$_sender_name,//'INFO',
			    'encoding'=>"0",
			    'schedule_time' => '',
			    'message' => $sms_body,
			    'recipients' => [array('recipient_id' => '1','dest_addr'=>$receiver_phone)]
			);
			//.... Api url
			$Url ='https://apisms.beem.africa/v1/send';//$this->accountModel->api_url;//'https://apisms.bongolive.africa/v1/send';

			// Setup cURL
			$ch = curl_init($Url);
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt_array($ch, array(
			    CURLOPT_POST => TRUE,
			    CURLOPT_RETURNTRANSFER => TRUE,
			    CURLOPT_HTTPHEADER => array(
			    	'api_key:'.$api_key,
			       'secrete_key:'.$secret_key,
			        'Authorization:Basic ' ."ZTgwYmE0NGE0ZGUxMGZmOTpZelUyWkRSaVpXTmhaV1ZrTVdJNE5XSm1Zall6TldKbE5qQmlOakptWmpka05HVXhNemszWlRWaVlUWXdZekV4TlRFNE5XSTFaRFF5TTJReVpEa3daQT09==" ,//base64_encode("$api_key:$secret_key"),
			        'Content-Type: application/json'
			        
			    ),
			    CURLOPT_POSTFIELDS => json_encode($postData)
			));

			// Send the request
			$response = curl_exec($ch);

			// Check for errors
			if($response === FALSE){
				echo "\n*ERROR\n";
			    var_dump($response);

			    die(curl_error($ch));
			    return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => "FALSE RESPONSE", 
                    "error_info"=>json_encode($response,true)
                ]; 
			}else{
				
				$r = json_decode($response);

				echo $r->code.":".$r->message;
				if((int)$r->code == 100){
					echo "\n*SENT\n";
					return [
	                    "response_type" => "SENT",//SENT/EXC//ERROR
	                    "response_info" => json_encode($response,true), 
	                    "error_info"=>''
	                ]; 
				}else{
					echo "\n*NOT SENT\n";
					return [
	                    "response_type" => "ERROR",//SENT/EXC//ERROR
	                    "response_info" => $r->message, 
	                    "error_info"=>json_encode($response,true)
	                ]; 
				}

				
			}
			


		}
		catch(\Exception $ex){
			return [
                "response_type" => "EXCEPTION",
                "response_info" => "ERROR",
                "error_info"=>$ex->getMessage()
            ];
		}

		
	}
	public function send_bulky_sms($config_model,$recepients_list,$sms_body,$_sender_name = "INFO"){
		
		try{
			ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
	        set_time_limit(0); //to prevent the script from dying
	        $this->accountModel = $config_model;
	        //echo $this->accountModel->customer_code;
	        echo "\n-- SMS Module -- \n";

	        //======================REQUIRED INFORMATION ============================
	        
	        date_default_timezone_set('Africa/Nairobi');
	        $sendername = ($_sender_name == "INFO" || $_sender_name == "info" || $_sender_name == null) ? $this->accountModel->api_sender_name : $_sender_name;//
	        $username = $this->accountModel->api_account_id;
	        $password = $this->accountModel->api_secrete_password;;
	        $apikey = $this->accountModel->api_key;
	        $apiSecretekey = $this->accountModel->api_token;
	       
	        $senddate = "";

	        echo "\n-> ". $this->accountModel->api_sender_name." : BULKY\n";
			
	        ///////////////////////////////////////////////////////////////////////////
			//.... replace <api_key> and <secrete_key> with the valid keys obtained from the platform, under profile>authentication information
			$api_key=$apikey;
			$secret_key = $apiSecretekey;
			// The data to send to the API
			$postData = array(
			    'source_addr' =>$_sender_name,//'INFO',
			    'encoding'=>0,
			    'schedule_time' => '',
			    'message' => $sms_body,
			    'recipients' => $this->getReceiversObjFromList($recepients_list),//[array('recipient_id' => '1','dest_addr'=>$receiver_phone)]
			);
			//.... Api url
			$Url =$this->accountModel->api_url;//'https://apisms.bongolive.africa/v1/send';

			// Setup cURL
			$ch = curl_init($Url);
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt_array($ch, array(
			    CURLOPT_POST => TRUE,
			    CURLOPT_RETURNTRANSFER => TRUE,
			    CURLOPT_HTTPHEADER => array(
			        'Authorization:Basic ' . base64_encode("$api_key:$secret_key"),
			        'Content-Type: application/json'
			    ),
			    CURLOPT_POSTFIELDS => json_encode($postData)
			));

			// Send the request
			$response = curl_exec($ch);

			// Check for errors
			if($response === FALSE){
				echo "\n*ERROR\n";
			    var_dump($response);

			    die(curl_error($ch));
			    return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => "FALSE RESPONSE", 
                    "error_info"=>json_encode($response,true)
                ]; 
			}else{
				
				$r = json_decode($response);

				echo $r->code.":".$r->message;
				if((int)$r->code == 100){
					echo "\n*SENT\n";
					return [
	                    "response_type" => "SENT",//SENT/EXC//ERROR
	                    "response_info" => json_encode($response,true), 
	                    "error_info"=>''
	                ]; 
				}else{
					echo "\n*NOT SENT\n";
					return [
	                    "response_type" => "ERROR",//SENT/EXC//ERROR
	                    "response_info" => $r->message, 
	                    "error_info"=>json_encode($response,true)
	                ]; 
				}

				
			}
			


		}
		catch(\Exception $ex){
			return [
                "response_type" => "EXCEPTION",
                "response_info" => "ERROR",
                "error_info"=>$ex->getMessage()
            ];
		}

		
	}



	/*
		SEND A SINGLE SMS (NON-XML FORMAT)
    */
	public function xxxsend_sms($config_model,$receiver_phone,$sms_body,$_sender_name = "INFO"){
		
		try{
			ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
	        set_time_limit(0); //to prevent the script from dying
	        $this->accountModel = $config_model;
	        //echo $this->accountModel->customer_code;
	        echo "\n-- SMS Module -- \n";

	        //======================REQUIRED INFORMATION ============================
	        
	        date_default_timezone_set('Africa/Nairobi');
	        $sendername = ($_sender_name == "INFO") ? $this->accountModel->api_phone_no : $_sender_name;//
	        $username = $this->accountModel->api_account_id;
	        $password = $this->accountModel->api_secrete_password;;
	        $apikey = $this->accountModel->api_key;
	       
	        $senddate = ""; 
			
	        //==========================END OF REQUIRED INFORMATION ====================
	        
	        
	        //==================OPTIONAL REQUIREMENTS =========================================
	        
	        $senddate = ""; //leave blank if you want an sms to be sent immediately or eg 31/03/2014 12:54:00 or 2014-03-31 12:54:00
	        $proxy_ip = ""; //leave blank if your network environment does not support proxy
	        $proxy_port = ""; //set your network port, leave black if your network environment does not support proxy
	        
	        //===================== END OF OPTIONAL REQUIREMENT ===========================
	        
	        
	        //===============================DO NOT EDIT ANYTHING BELOW ===================
	        
	        $sendername = urlencode($sendername);
	        $apiKey = urlencode($apikey);
	        $destnum = urlencode($this->bongoLiveSmsFormat($receiver_phone));
	        $message = urlencode($sms_body);

			/*
	        if(!empty($senddate)) {
	            $senddate = strtotime("2014-05-03 13:50:00");
	        }
			*/

	        $posturl = "http://www.bongolive.co.tz/api/sendSMS.php?sendername=$sendername&username=$username&password=$password&apikey=$apiKey&destnum=$destnum&message=$message&senddate=$senddate";
			
	        $ch = curl_init();
	        curl_setopt($ch, CURLOPT_URL, $posturl);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 500); //tim

	        if ($proxy_ip !="") {
	            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
	            curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
	            curl_setopt($ch, CURLOPT_PROXY, $proxy_ip);
	        }

	        $response = curl_exec($ch);
	        
	        //===============YOU CAN EDIT BELOW ===
	       
		    if ($response == 1)
			{
				return [
                    "response_type" => "SENT",//SENT/EXC//ERROR
                    "response_info" => $this->bongolive_response_code_defination($response), 
                    "error_info"=>''
                ]; 
			}
			else if ($response== 0)
			{
				//echo "here = 0";
				$response = "Message Sent";
				return [
                    "response_type" => "SENT",//SENT/EXC//ERROR
                    "response_info" => $this->bongolive_response_code_defination($response), 
                    "error_info"=>''
                ]; 
			}
			else if ($response== '0')
			{
				return [
                    "response_type" => "SENT",//SENT/EXC//ERROR
                    "response_info" => $this->bongolive_response_code_defination($response), 
                    "error_info"=>''
                ]; 
			}
			else{
				//Successful - Msg Sent
				//if(){

				//}else{

				//}
				return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => $this->bongolive_response_code_defination($response), 
                    "error_info"=>$this->bongolive_response_code_defination($response)
                ]; 
			}


		}
		catch(\Exception $ex){
			return [
                "response_type" => "EXCEPTION",
                "response_info" => "ERROR",
                "error_info"=>$ex->getMessage()
            ];
		}

		
	}


	/*
		* send bulky sms 
	*/
	public function old_send_bulky_sms($config_model,$recepients_list,$sms_body,$_sender_name = "INFO"){
		try{
			ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
	        set_time_limit(0); //to prevent the script from dying
	        $this->accountModel = $config_model;
	        //echo $this->accountModel->customer_code;
	        echo "\n-- SMS BULKY Module -- \n";
	        
	        date_default_timezone_set('Africa/Nairobi');
	        if(!defined('URL_API_DOMAIN')){
	        	define ("URL_API_DOMAIN", "http://www.bongolive.co.tz/api/broadcastSMS.php");
	        }
	        
	        $sendername = ($_sender_name == "INFO") ? $this->accountModel->api_phone_no : $_sender_name;//$this->accountModel->api_phone_no;//
	        $username = $this->accountModel->api_account_id;
	        $password = $this->accountModel->api_secrete_password;;
	        $apikey = $this->accountModel->api_key;

		    //$callbackURL = "http://922c9e59.ngrok.io/callback_dlr.php"; 
		    $messageXML = "
				<Broadcast> 
				    <Authentication>
				        <Sendername>".$sendername."</Sendername>
				        <Username>".$username."</Username>
				        <Password>".$password."</Password>
				        <Apikey>".$apikey."</Apikey> 
				    </Authentication>
				        <Message>
				            <Content>".$sms_body."</Content>
				            <Receivers>
					            ".$this->getReceiversXMLTagsFromList($recepients_list)."
				            </Receivers>
				                <Callbackurl><Url></Url></Callbackurl>
				        </Message>
				 </Broadcast>";
		    $data = array('messageXML' => $messageXML);
		    $ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, URL_API_DOMAIN);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		  $response = curl_exec($ch);
		  echo "response = $response ";
		  if($response == ""){
		  		return [
                    "response_type" => "RETRY",//SENT/EXC//ERROR
                    "response_info" => "<Empty Response>", 
                    "error_info"=>'<Empty Result>',
                    "units_consumed" => 0,
                ];
		  }
		  else{
		  	$parsed = simplexml_load_string($response);
		  	$content = json_decode(json_encode($parsed),TRUE);
		  	//echo "----\n";
			var_dump("CODE:".$content['Response']['code']." ==> ".$content['Response']['message']); 

			if($content['Response']['code'] >= 0){

				//when sms sent to x customers
				return [
                    "response_type" => "SENT",//SENT/EXC//ERROR
                    "response_info" => $this->bongolive_response_code_defination($content['Response']['code']), 
                    "error_info"=>'',
                    "units_consumed" => (int)$content['Response']['code'],//how many recepient
                ];
			} 
			else{
				return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => $this->bongolive_response_code_defination($content['Response']['code']), 
                    "error_info"=>'',
                    "units_consumed" => 0,
                ];
			}
		  }

	    }
	    catch(\Exception $ex){
	    	return [
                "response_type" => "EXCEPTION",
                "response_info" => "ERROR",
                "error_info"=>$ex->getMessage(),
                "units_consumed" => 0,
            ];
	    }
	}
 
	


	private function getReceiversObjFromList($list){
		$all_tags = [];
		for($i = 0; $i < sizeof($list);$i++){
			//
			array_push($all_tags, array('recipient_id' => '1','dest_addr'=>$this->bongoLiveSmsFormat($list[$i])));
		}
		return $all_tags;
	} 
	/*
		eg
		<Receiver>+255688059688</Receiver>
		<Receiver>+255688059688</Receiver>
	*/
	private function getReceiversXMLTagsFromList($list){
		$all_tags = "";
		for($i = 0; $i < sizeof($list);$i++){
			//
			$all_tags .= "<Receiver>".$this->bongoLiveSmsFormat($list[$i])."</Receiver>";
		}
		return $all_tags;
	} 


	/* 
		Bongolive SMS Format
	*/
	private function bongoLiveSmsFormat($phone,$country_code = 'TZ'){
		if($country_code == 'TZ'){
			$phone = str_replace('-', '', $phone);//remove dashes
			$phone = preg_replace('/\s+/', '', $phone);//remove white space

			if(substr($phone, 0,4) == "+255"){ 
			}
			else if(substr($phone, 0,3) == "255"){
				$phone = "+2".substr($phone, 1,strlen($phone) - 1);
			}
			else if(substr($phone, 0,1) == "0"){
				$phone = "+255".substr($phone, 1,strlen($phone) - 1);
			}
			return $phone;
		}
		else{
			return $phone;
		}
	}





}

/*
	
	100	Message Submitted Successfully	
Message has been submitted to the queue for processing.

101	Invalid phone number	
Invalid recipient dest_addr

102	Insufficient balance	
Balance is not enough for Sending messages

103	Network timeout	
104	Please provide all required parameters	
Missing Required Parameters

105	Account not found	
User credentials not found

106		
Placeholder

107	No authorization headers	
Request is missing Authorization Header

108	Invalid token	
Invalid token provided on authorization header

109	Invalid Message	
Invalid message content or empty message

110	Enter a plain text message only. Special characters(Unicode) are not supported.	
Message contains special characters

111	Invalid Sender ID	
Sender ID not active/registered

112	The message cannot be scheduled for a date/time in the past	
Scheduled date/time must be greater than current date/time

113	Invalid request format	
Invalid request format this apply on api sms both xml and json format

114	Recipients exceed the Limit of 1000	
Api sms not allow user to send more than 1000 contacts

115	Destination Number is Missing	
Destination address not provided

116	Missing/Invalid Reference Id	
Reference id must be provided on api sms, this will be used to send callback

117	Invalid Scheduled Time	
Confirm that the scheduled time is in the correct format. ‘YYYY-MM-DD HH:MM’

118	Missing encoding type	
Default encoding value is 0. Other encoding types are currently not supported.

119	Missing recipient(s) tag	
120	Invalid Authentication Parameters	
Please confirm your authentication parameters API Key and Secret Key.

	*/
?>