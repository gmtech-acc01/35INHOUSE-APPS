<?php
namespace App\Modules; 

//require_once("./../../vendor/autoload.php");
use AfricasTalking\SDK\AfricasTalking;

/**/
use App\Models\Core\AccountMail;

/*
	model info:
	req : username,apikey,from
	match: [api_channel],[api_key],[api_sender_name] - from queue DB

*/
class AfriTalkSMS{

	public function __construct(){
		
	}

	public function send_sms($config_model,$receiver_phone,$sms_body){

		$this->accountModel = $config_model;
        //echo $this->accountModel->customer_code;
        echo "-- SMS Module -- \n";

        $username = $this->accountModel->api_channel;
		$apiKey = $this->accountModel->api_key;
		$from = $this->accountModel->api_sender_name;
		$receiver_phone = $this->afriTalkFormat($receiver_phone);


		$username   = $username;
		$apiKey     = $apiKey;

		// Initialize the SDK
		$AT         = new AfricasTalking($username, $apiKey);

		// Get the SMS service
		$sms        = $AT->sms();

		// Set the numbers you want to send to in international format
		$recipients = $receiver_phone;//"+254711XXXYYY,+254733YYYZZZ";

		// Set your message
		$message    = $sms_body;

		// Set your shortCode or senderId
		$from       = $from;

		try {
		    // Thats it, hit send and we'll take care of the rest
		    $result = $sms->send([
		        'to'      => $recipients,
		        'message' => $message,
		        'from'    => $from
		    ]);

		    print_r($result);
		    if($result['status'] == "success"){
		    	 return [
                    "response_type" => "SENT",
                    "error_info"=>"",
                    "response_info"=> "OK"
                ];
		    }else{
		    	return [
                    "response_type" => $result['status'],//"ERROR",
                    "response_info" => "FAILED", 
                    "error_info"=>json_encode($result)//$mail->ErrorInfo
                ]; 
		    }
		} catch (Exception $e) {
		    echo "Error: ".$e->getMessage();
		    return [
                "response_type" => "EXCEPTION",
                "response_info" => "ERROR",
                "error_info"=>$e->getMessage()
            ];
		}

	}
	//end send sms

	private function afriTalkFormat($phone){
		$phone = str_replace('-', '', $phone);//remove dashes
		$phone = preg_replace('/\s+/', '', $phone);//remove white space

		if(substr($phone, 0,4) == "+255"){
			//$phone = "255".substr($phone, 4,strlen($phone) - 4);
		}
		else if(substr($phone, 0,3) == "255"){
		}
		else if(substr($phone, 0,3) == "254"){
			$phone = "+" .$phone;
		}
		else if(substr($phone, 0,1) == "0"){
			//$phone = "255".substr($phone, 1,strlen($phone) - 1);
		}
		return $phone;
	}
}


//$m = new AfriTalkSMS();
//$m->send_sms(null,"+254741067804","Yo Zico,Deo Here saying Hi");