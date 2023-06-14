<?php
namespace App\Workers\Email;
use App\Modules\GMailerModule;
use App\Modules\GoDaddyMailerModule;
use App\Modules\GSuiteMailerModule;



  
 
use App\Models\Core\AccountMail;



class MailWorker{


	public function __construct() {
    }


    public function send($acc_name,$g_list,$g_cc_list,$g_bcc_list,$in_reply_to_email= "",$in_reply_to_title="",$header,$sub,$body,$isHTML = true){
    	$account = AccountMail::where('acc_no',$acc_name)->first();

    	if($account != null){
            //
            if($account->provider == "GODADDY"){
                $mail_worker = new GoDaddyMailerModule();
                //return $mail_worker->SEND_MAIL($account,$g_list,$g_cc_list,$g_bcc_list,$header,$sub,$body,$isHTML);
                return $mail_worker->SEND_MAIL($account,$g_list,$g_cc_list,$g_bcc_list,$in_reply_to_email,$in_reply_to_title,$header,$sub,$body,$isHTML);
            }
            else if($account->provider == "GSUITE"){
                $mail_worker = new GSuiteMailerModule();
                return $mail_worker->SEND_MAIL($account,$g_list,$g_cc_list,$g_bcc_list,$in_reply_to_email,$in_reply_to_title,$header,$sub,$body,$isHTML);
            }
            else{
                $mail_worker = new GMailerModule();
                //return $mail_worker->SEND_MAIL($account,$g_list,$g_cc_list,$g_bcc_list,$header,$sub,$body,$isHTML);
                return $mail_worker->SEND_MAIL($account,$g_list,$g_cc_list,$g_bcc_list,$in_reply_to_email,$in_reply_to_title,$header,$sub,$body,$isHTML);
            }

    		
    	}else{
    		//$mail_worker = new GMailerModule();
    		//$mail_worker->SEND_MAIL(AccountMail::default_mail(),$g_list,$g_cc_list,$g_bcc_list,$header,$sub,$body,$isHTML);
            return [
                    "response_type" => "INVALID-ACCOUNT",
                    "response_info" => "", 
                    "error_info"=>"Account is invalid!!"
                ];
    	}
    }


    //

    
}





