<?php
namespace App\Workers\SMS_BULKY;
 
use App\Modules\SMSNexmoModule;
use App\Modules\SMSFastHub;
use App\Modules\SMSMoveSms;
use App\Modules\SMSTwilioModule;
use App\Modules\SMSBongoLiveModule;


/*BOOTING*/ 
/*require_once("./../../../vendor/autoload.php");
use App\Models\Core\Database;
//Initialize Illuminate Database Connection
new Database("grand_queue_manager");*/

use App\Models\Core\AccountSms;


class SMSBulkyWorker{


	public function __construct() {
    }
    
    public function send_sms($acc_no,$receiver_phone,$sms_body,$_sender_name ="INFO"){
    	
        //load account details to view the provider
    	$account = AccountSms::where('acc_no',$acc_no)->first();
    	if($account != null){
            //
            if($account->provider == "TWILIO"){
                $worker = new SMSTwilioModule();
                return $worker->send_sms($account,$receiver_phone,$sms_body);
            }
            else if($account->provider == "NEXMO"){
                $worker = new SMSNexmoModule();
                return $worker->send_sms($account,$receiver_phone,$sms_body);
            }
            else if($account->provider == "MOVESMS"){
                $worker = new SMSMoveSms();
                return $worker->send_sms($account,$receiver_phone,$sms_body);
            }
            else if($account->provider == "BONGO-LIVE"){
                $worker = new SMSBongoLiveModule();
                return $worker->send_sms($account,$receiver_phone,$sms_body,$_sender_name);
            }
            else{
                return null;
            	//BONGO LIVE
                //$worker = new SMSBongoLiveModule();
                //return $worker->send_sms($account,$receiver_phone,$sms_body);
            }
    	}else{
            echo "<INVALID ACCOUNT>";
            return  null;
    		//$worker = new SMSFastHub();
            //$worker->send_sms($account,$receiver_phone,$sms_body);
    	}
    }

    public function send_bulky_sms($acc_no,$recepientList,$sms_body,$_sender_name ="INFO"){
        
        //load account details to view the provider
        $account = AccountSms::where('acc_no',$acc_no)->first();
        if($account != null){
            //
            if($account->provider == "xTWILIO"){
                $worker = new SMSTwilioModule();
                return $worker->send_bulky_sms($account,$recepientList,$sms_body);
            }
            else if($account->provider == "xNEXMO"){
                $worker = new SMSNexmoModule();
                return $worker->send_bulky_sms($account,$recepientList,$sms_body);
            }
            else if($account->provider == "xMOVESMS"){
                $worker = new SMSMoveSms();
                return $worker->send_bulky_sms($account,$recepientList,$sms_body);
            }
            else if($account->provider == "BONGO-LIVE"){
                $worker = new SMSBongoLiveModule();
                return $worker->send_bulky_sms($account,$recepientList,$sms_body,$_sender_name);
            }
            else{
                return null;
                echo "<INVALID INVALID-BULKY-SMS-PROVIDER>";
                return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => "ACC-ERROR:INVALID-PROVIDER",
                    "error_info"=>'ACC-ERROR:INVALID-SMS-PROVIDER',
                    "units_consumed"=>0,
                ]; 
            }
        }else{
           echo "<INVALID ACCOUNT>";
            return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => "ACC-ERROR:INVALID-ACCOUNT",
                    "error_info"=>'ACC-ERROR:INVALID-ACCOUNT',
                    "units_consumed"=>0,
                ]; 
        }
    }



    
}

/*
    Listener -> listen from a queue
    Listerner -> call -> worker
    worker -> call specific module(sms/email module)
*//*
$m = new SMSBulkyWorker;
$r = $m->send_bulky_sms("HASSANBL01",["255688059688"],"Greetings Brother.-Gmtech Development Team");
var_dump($r);*/

?>





