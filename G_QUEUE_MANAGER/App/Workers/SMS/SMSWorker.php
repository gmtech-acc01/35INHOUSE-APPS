<?php
namespace App\Workers\SMS;
 
 /*BOOTING*/ 
/*require_once("./../../../vendor/autoload.php");
use App\Models\Core\Database;
//Initialize Illuminate Database Connection
new Database("grand_queue_manager");*/

use App\Modules\SMSNexmoModule;
use App\Modules\SMSFastHub;
use App\Modules\SMSMoveSms;
use App\Modules\SMSTwilioModule;
use App\Modules\SMSBongoLiveModule;
//use App\Modules\SMSBongoLiveModuleV2;
use App\Modules\SMSGHTTPAndroidServerModule;
use App\Modules\AfriTalkSMS;



use App\Models\Core\AccountSms;
 

class SMSWorker{


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
            else if($account->provider == "AFRICAS-TALKING"){
                $worker = new AfriTalkSMS(); 
                return $worker->send_sms($account,$receiver_phone,$sms_body);//$receiver_phone:: it allows multi phone nos
            }
            else if($account->provider == "MOVESMS"){
                $worker = new SMSMoveSms();
                return $worker->send_sms($account,$receiver_phone,$sms_body);
            }
            else if($account->provider == "BONGO-LIVE"){
                $worker = new SMSBongoLiveModule();
                return $worker->send_sms($account,$receiver_phone,$sms_body,$_sender_name);
            }
            else if($account->provider == "BEEM"){
                $worker = new SMSBongoLiveModule();
                return $worker->send_sms($account,$receiver_phone,$sms_body,$_sender_name);
            }
            else if($account->provider == "G-HTTP-ANDROID-SERVER"){
                $worker = new SMSGHTTPAndroidServerModule();
                return $worker->send_sms($account,$receiver_phone,$sms_body);
            }
            else{
                echo "<INVALID INVALID-SMS-PROVIDER>";
                return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => "ACC-ERROR:INVALID-PROVIDER",
                    "error_info"=>'ACC-ERROR:INVALID-SMS-PROVIDER',
                ]; 
            }
    	}else{
            echo "<INVALID ACCOUNT>";
            return [
                    "response_type" => "ERROR",//SENT/EXC//ERROR
                    "response_info" => "ACC-ERROR:INVALID-ACCOUNT",
                    "error_info"=>'ACC-ERROR:INVALID-ACCOUNT',
                ]; 
    		//$worker = new SMSFastHub();
            //$worker->send_sms($account,$receiver_phone,$sms_body);
    	}
    }


    
}

/*
    Listener -> listen from a queue
    Listerner -> call -> worker
    worker -> call specific module(sms/email module)
*/
$m = new SMSWorker; //GMTWL01
//$m->send_sms("VIFURUSHI","255788449030","Greetings Brother.\n@Gmtech Development Team","Vifurushi");
//$m->send_sms("DELTA01","+254741067804","Greetings Brother.\n@Gmtech Development Team"); 
//$m->send_sms("VIFURUSHI",["0684983533","255788449030"],"Test@Gmtech Development Team","Vifurushi");

//$m->send_sms("GMTWL01","+255788449030","TWILIO TEST.\n@Gmtech Development Team"); 


?>





