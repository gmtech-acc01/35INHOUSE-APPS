<?php
namespace App\Modules; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



class MailerModule{
    public function __construct() {
    }


    public function SEND_MAIL($g_to_list,$g_subject,$g_body,$g_isHtml = true){
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
        $mail->Host = 'smtp.gmail.com';//
        //$mail->Host = 'mail.dar-averi.com';//
        $mail->Port = 587;

        $mail->Username = 'grand123grand1@gmail.com';
        //mail->Username = 'deograciousngereza@dar-averi.com';
        $mail->Password = 'Dangerboy@123'; //emjeys.devs@12345
        $mail->setFrom('grand123grand1@gmail.com', 'Emjeys');
        
        for($i = 0; $i <sizeof($g_to_list);$i++){
            //$mail->addAddress('deograciousngereza@gmail.com');   // Add a recipient
            $mail->addAddress($g_to_list[$i]);
        }

        $mail->isHTML($g_isHtml);  // Set email format to HTML

        $bodyContent = $g_body;
        $mail->Subject = $g_subject;
        $mail->Body    = $bodyContent;
        if(!$mail->send()) {
          return "".$mail->ErrorInfo;
        } else {
          return "Msg send";
        }
    }


};

    

   

?>