<?php
namespace App\Modules; 

use Twilio\Rest\Client;

class SMSTwilioModule{


	public function __construct(){
		
	}
 
	/*
		SEND A SINGLE SMS 
    */
	public function send_sms($config_model,$receiver_phone,$sms_body){

		ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
        set_time_limit(0); //to prevent the script from dying
        $this->accountModel = $config_model;
        //echo $this->accountModel->customer_code;
        echo "-- SMS Module -- \n";

        
		try {
			$client = new Client($this->accountModel->api_account_id, $this->accountModel->api_token);
 
			$response = $client->messages->create(
			    $receiver_phone,
			    array(
			        "from" => $this->accountModel->api_phone_no,//$this->twilio_phone_number,
			        "body" => $sms_body 
			    )
			);
			//TODO::read from response 
			//var_dump($response);

			return [
                "response_type" => "SENT",//SENT/EXC//ERROR
                "response_info" => json_encode($response,true), 
                "error_info"=>''
            ]; 

		}catch(\Exception $ex){
			echo "\nTwilio - Error";
			echo "\nMessage:". $ex->getMessage();
			return [
                "response_type" => "EXCEPTION",
                "response_info" => "ERROR",
                "error_info"=>$ex->getMessage()
            ];
		}

	}





	





}
?>