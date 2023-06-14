<?php
require_once __DIR__ . './../vendor/autoload.php';
require_once __DIR__ . './mail_template.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


function getSysRefToken($length = 18){

     $token = "";
     //$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
     $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
     $codeAlphabet.= "0123456789";
     $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[random_int(0, $max-1)];

    }

    return date('dmYHis').'.'.$token;
}




echo "*** PHP R-MQ SENDER ***\n\n";
error_reporting(E_ERROR | E_PARSE);

 
try{
 
	//create a connection
	//$connection = new AMQPStreamConnection('35.154.93.158', 5672, 'grand', 'password');
	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
	$channel = $connection->channel();

	
	//channel declaration
	//$channel->queue_declare('gEmailNotifier', true, false, false, false);

	$channel->queue_declare(
	    'gSmsNotifier',    //queue - Queue names may be up to 255 bytes of UTF-8 characters
	    false,              //passive - can use this to check whether an exchange exists without modifying the server state
	    true,               //durable, make sure that RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart
	    false,              //exclusive - used by only one connection and the queue will be deleted when that connection closes
	    false               //auto delete - queue is deleted when last consumer unsubscribes
    );
    


//$tpl_ui
	//send a message 
	$s = json_encode(
		[ 
			"account_no"=>"GMBL02",//"GMBL02",//"GMBL01",//"GMANDR01",//GMFH01 //"GMNXM01",//"MOVESMS01","GMTWL01","GMBL01","GMFH01","GMANDR01"
			"receiver_phone" => "255788449030",//"+255758083816",//"+255788449030",//"+254741067804",
			"message"=> "TEST",
			"sys_ref"=>getSysRefToken()
		]
	);
	//$msg = new AMQPMessage($s);
	$msg = new AMQPMessage(
    	$s,
    	array('delivery_mode' => 2) # make message persistent, so it is not lost if server crashes or quits
    );
	$channel->basic_publish($msg, '', 'gSmsNotifier');

	echo " [x] Msg Sent.'\n";


	$channel->close();
	$connection->close();

	echo " \n[ Other tasks ] ";

}
catch(\Exception $x){
	//r-mq server is down
	echo "[ R-MQ ERROR ]";
	echo "Message:". $x->getMessage();
}




?>