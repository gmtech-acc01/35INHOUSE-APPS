<?php
namespace App\Modules; 

class SMSFastHub{


	public function __construct(){
		
	}
 



	/*
		SEND A SINGLE SMS 
    */
	public function send_sms($config_model,$receiver_phone,$sms_body){

		/*return [
                    "response_type" => "SENT",
                    "error_info"=>"",
                    "response_info"=> "OK"
                ];*/
                
		ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
        set_time_limit(0); //to prevent the script from dying
        $this->accountModel = $config_model;
        //echo $this->accountModel->customer_code;
        echo "-- SMS Module -- \n";

        $url = $this->accountModel->api_url;
		$password = $this->accountModel->api_secrete_password;
		$channel = $this->accountModel->api_channel;
		$source = $this->accountModel->api_sms_source;
		$receiver_phone = $this->fashHubSmsFormat($receiver_phone);



        $ch = curl_init(); 

		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POST, 1);
		
		
		$sms_data = [
          	"channel" => [
          		"channel"  => $channel,
          		"password" => $password
          	],
          	"messages" => [
          		[
          			'text' => $sms_body,
          			'msisdn' => $this->fashHubSmsFormat($receiver_phone),
          			'source' => $source//sender name
          		]
          	]
        ];
       

		
		// In real life you should use something like:http_build_query 
		curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($sms_data));  
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
		


		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		echo "OUT>>".$server_output;
		curl_close ($ch);
	}





	/*
		OLDER FAST HUN SMS SAMPLE FUNCTION
	*/
	public function xxxsend_sms($receiver_phone,$sms_body){

		$receiver_phone = $this->fashHubSmsFormat($receiver_phone);
		/*$_h = curl_init();		curl_exec($_h);*/

		/*curl_setopt($_h, CURLOPT_HEADER, 1);
		curl_setopt($_h, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($_h, CURLOPT_HTTPGET, 1);
		curl_setopt($_h, CURLOPT_URL, 'https://api.twilio.com' );
		curl_setopt($_h, CURLOPT_PROXY, "http://b29beb6c.ngrok.io");
		curl_setopt($_h, CURLOPT_DNS_USE_GLOBAL_CACHE, false );
		curl_setopt($_h, CURLOPT_DNS_CACHE_TIMEOUT, 2 );
		//curl_setopt($_h, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($_h, CURLOPT_SSL_VERIFYHOST, 2);*/

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,"http://167.114.34.196/fasthub/messaging/json/api");
		curl_setopt($ch, CURLOPT_POST, 1);
		//curl_setopt($ch, CURLOPT_POSTFIELDS,  "postvar1=value1&postvar2=value2&postvar3=value3");

		
		// In real life you should use something like:http_build_query
		curl_setopt($ch, CURLOPT_POSTFIELDS, 
	        [
	          	"channel" => [
	          		"channel" => 118294,
	          		"password" => "ZWMwOTI5NjQ1OTZiMjVlMTVlMTc3MDUwY2VkNWVkMTA4NWY0MzM3YjVkZmQyN2M2N2MyNTNkZjY1NjdhYzUwMg=="
	          	],
	          	"messages" => [
	          		"text"=>$sms_body,
	          		"msisdn"=>$receiver_phone,
	          		"source"=>"SMSAuth"
	          	]
	        ]
		);

		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec($ch);
		curl_close ($ch);

		
	}

	/*
		FASTHUB requires mobile no to start with 255.....
	*/
	private function fashHubSmsFormat($phone){
		$phone = str_replace('-', '', $phone);//remove dashes
		$phone = preg_replace('/\s+/', '', $phone);//remove white space

		if(substr($phone, 0,4) == "+255"){
			$phone = "255".substr($phone, 4,strlen($phone) - 4);
		}
		else if(substr($phone, 0,3) == "255"){
		}
		else if(substr($phone, 0,1) == "0"){
			$phone = "255".substr($phone, 1,strlen($phone) - 1);
		}
		return $phone;
	}





}
?>