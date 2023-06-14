<?php
namespace App\Modules; 

class SMSMoveSms{


	public function __construct(){
		
	}
 

//https://sms.movesms.co.ke/api/compose?username=Ngereza&api_key=QRZULI4HzsZVkGa6fOv9VWYD95G0eQutgAapWigPetxTreYugT&sender=SMARTLINK&to=[Your+Recipients]&message=[Your message]&msgtype=[Type of the message]&dlr=[Type of Delivery Report]

	/*
		SEND A SINGLE SMS 
    */
	public function send_sms($config_model,$receiver_phone,$sms_body){

		ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
        set_time_limit(0); //to prevent the script from dying
        $this->accountModel = $config_model;
        //echo $this->accountModel->customer_code;
        echo "-- SMS Module -- \n";

        $url = $this->accountModel->api_url;
		$password = $this->accountModel->api_secrete_password;
		
		$username = $this->accountModel->api_account_id;
		$api_key = $this->accountModel->api_key;
		$sender = $this->accountModel->api_sms_source;
		$to = $this->moveSmsFormat($receiver_phone);
		$msgtype = 5;
		$dlr = 0;


		$query = http_build_query([
			'username' => $username,
			'api_key' =>$api_key,
			'sender' => $sender,
			'to' => $to,
			'message' => $sms_body,
			'msgtype' => $msgtype,
			'dlr' =>  $dlr
		]);

		$url = 'https://sms.movesms.co.ke/api/compose?'.$query;
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$server_output = curl_exec($ch);
		curl_close($ch);
		/*if (curl_errno($ch)) {
		    echo 'Error: ' . curl_error($ch);
		}
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		//var_dump($http_status);
		//var_dump($server_output);*/
       

	}





	/* 
		FASTHUB requires mobile no to start with 255.....
	*/
	private function moveSmsFormat($phone){
		$phone = str_replace('-', '', $phone);//remove dashes
		$phone = preg_replace('/\s+/', '', $phone);//remove white space

		if(substr($phone, 0,4) == "+254"){
		}
		else if(substr($phone, 0,3) == "254"){
			$phone = "+2".substr($phone, 1,strlen($phone) - 1);
		}
		else if(substr($phone, 0,1) == "0"){
			$phone = "+254".substr($phone, 1,strlen($phone) - 1);
		}
		return $phone;
	}





}
?>