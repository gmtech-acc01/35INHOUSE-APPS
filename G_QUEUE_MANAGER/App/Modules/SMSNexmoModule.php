<?php
namespace App\Modules; 


class SMSNexmoModule{

	private $accountModel;//AccountMail instance

    public function __construct() {
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

		$API_KEY = $this->accountModel->api_key;
		$API_SECRET = $this->accountModel->api_secrete_password;
        $client = new \Nexmo\Client(new \Nexmo\Client\Credentials\Basic($API_KEY, $API_SECRET));
		
		$message = $client->message()->send([
		    'to' => $this->getRightSmsFormat($receiver_phone),
		    'from' => 'Nexmo',//$this->accountModel->api_sms_source,
		    'text' => $sms_body
		]);
	}






	/*
		FASTHUB requires mobile no to start with 255.....
	*/
	private function getRightSmsFormat($phone){
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



/*
links
Non White-listed Destination - rejected

*/

}
?>

