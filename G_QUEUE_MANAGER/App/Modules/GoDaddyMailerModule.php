<?php
namespace App\Modules; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/**/
use App\Models\Core\AccountMail;



class GoDaddyMailerModule{

    private $accountModel;//AccountMail instance

    public function __construct() {
    }

 
    public function SEND_MAIL($config_model,$g_to_list,$g_cc_list,$g_bcc_list,$in_reply_to_email = "",$in_reply_to_title="Customer-Service",$g_header,$g_subject,$g_body,$g_isHtml = true){


        try{
            ignore_user_abort(true); // Ignore user aborts and allow the script to run forever
            set_time_limit(0); //to prevent the script from dying

            $this->accountModel = $config_model;


            //echo $this->accountModel->customer_code;

            echo "MailerModule-- \n";
            $mail = new PHPMailer;
            $mail->isSMTP();                            // Set mailer to use SMTP

            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true 
                )
            );
            
            $mail->SMTPDebug = 1;  // debugging: 1 = errors and messages, 2 = messages only
            $mail->SMTPAuth = true;  // authentication enabled
            $mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
            $mail->SMTPAutoTLS = false;
            $mail->Host = $this->accountModel->host;
            $mail->Port = $this->accountModel->port;

            
            $mail->Username = $this->accountModel->sender_email;
            $mail->Password = $this->accountModel->sender_password;
            $mail->setFrom($this->accountModel->from, $g_header);
            $mail->FromName = $g_header;

            //
            if(strlen($in_reply_to_email) > 3){
                $mail->addCustomHeader( 'In-Reply-To', '<' . $in_reply_to_email . '>' );
                $mail->AddReplyTo($in_reply_to_email,$in_reply_to_title);
            }
            
            

            for($i = 0; $i <sizeof($g_to_list);$i++){
                $mail->addAddress($g_to_list[$i]);
            }
            for($i = 0; $i <sizeof($g_bcc_list);$i++){
                $mail->AddBCC($g_bcc_list[$i]);
            }
            for($i = 0; $i <sizeof($g_cc_list);$i++){
                $mail->AddCC($g_cc_list[$i]);
            }

            $mail->isHTML($g_isHtml);  // Set email format to HTML

            $bodyContent = $g_body;
            $mail->Subject = $g_subject;
            $mail->Body    = $bodyContent;

            if(!$mail->send()) {
                return [
                    "response_type" => "ERROR",
                    "response_info" => "FAILED", 
                    "error_info"=>$mail->ErrorInfo
                ]; 

            }
            else {
                return [
                    "response_type" => "SENT",
                    "error_info"=>"",
                    "response_info"=> "OK"
                ];
            }

        }
        catch(\Exception $ex){
            return [
                "response_type" => "EXCEPTION",
                "response_info" => "ERROR",
                "error_info"=>$x->getMessage()
            ];
        }

        
    }


};

    

   

?>