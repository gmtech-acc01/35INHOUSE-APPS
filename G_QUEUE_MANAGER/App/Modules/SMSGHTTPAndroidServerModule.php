<?php
namespace App\Modules; 


/*
	this is android app smsserver installed

*/
class SMSGHTTPAndroidServerModule{


	public function __construct(){
		
	}
 

	/*
		SEND A SINGLE SMS 
    */
	public function send_sms($config_model,$receiver_phone,$sms_body){

		try{
			ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
	        set_time_limit(0); //to prevent the script from dying
	        $this->accountModel = $config_model;
	        //echo $this->accountModel->customer_code;
	        echo "-- SMS Module -- \n";
	        //$url = "http://192.168.43.168:5025/SendSMS/user=DFLT&password=123456&phoneNumber="+$receiver_phone+"&msg=" + $new_msg;
		

	        $url = $this->accountModel->api_url;//api url
			$password = $this->accountModel->api_secrete_password;//password
			$username = $this->accountModel->api_account_id;//username
			$to = $this->smsFormat($receiver_phone);

			$query = http_build_query([
				'msg' => $sms_body
			]);
			

			$api_url = $url."/SendSMS/user=".$username."&password=".$password."&phoneNumber=".$receiver_phone."&".$query;
	        $ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $api_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$server_output = curl_exec($ch);
			curl_close($ch);
			echo $api_url;
			
			if($server_output == "OK"){
				echo "--SMS SENT --";
				return [
                    "response_type" => "SENT",//SENT/EXC//ERROR
                    "response_info" => "OK", 
                    "error_info"=>''
                ]; 
			}else{
				return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => "ERROR", 
                    "error_info"=>'ERROR'
                ]; 
			}

			//var_dump($http_status);*/
			//var_dump($server_output);
		}
		catch(\Exception $ex){
			echo "\nERROR::".$ex->getMessage();
			return [
                "response_type" => "EXCEPTION",
                "response_info" => "ERROR",
                "error_info"=>$ex->getMessage()
            ];
		}

		
       

	}





	/* 
		Android requires mobile no to start with 255/+255.....
	*/
	private function smsFormat($phone){
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





}
?>