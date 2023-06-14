<?php
namespace App\Workers\SMS;


use App\Modules\TwilioSms;
use App\Modules\FastHubSms;



class SMSWorker{

	private $default_sms_provider = "FASTHUB";//TWILIO/FASTHUB

	private $sms_activated = true;

	public function __construct(){
		
	}




	public function send_sms($receiver_phone,$sms_body){

		if($this->sms_activated == false) return "";

		switch ($this->default_sms_provider) {

			case 'TWILIO':
				$smsProvider = new TwilioSms();
				try{
					$smsProvider->send_sms($this->getValidPhoneNo(trim($receiver_phone)),$sms_body);
				}catch(Exception $e){
					//::TODO record sms error
				}
				break;
			case 'FASTHUB':
				$smsProvider = new FastHubSms();
				try{
					$smsProvider->send_sms($this->getValidPhoneNo(trim($receiver_phone)),$sms_body);
				}catch(Exception $e){
					//::TODO record sms error
				}
				break;
			
			default:
				# code...
				break;
		}
	}

	




	public function getValidPhoneNo($phone){
		if($phone[0] == '0'){
			$valid_phone = '+255'.ltrim($phone, '0');//TZ country code no
			return $valid_phone;
		}else{
			return $phone;
		}
	}







}
?>





