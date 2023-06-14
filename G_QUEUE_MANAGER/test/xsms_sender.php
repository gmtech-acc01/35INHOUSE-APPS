<?php
require_once __DIR__ . './../vendor/autoload.php';
require_once __DIR__ . './mail_template.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



echo "*** PHP R-MQ SMS SENDER ***\n\n";
error_reporting(E_ERROR | E_PARSE);


try{

	//create a connection
	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
	$channel = $connection->channel();

	
	//channel declaration
	$channel->queue_declare('emjeysSmsQ', true, false, false, false);


//$tpl_ui
	//send a message 
	$s = json_encode(
		[
			"receiver_phone" => "+255788449030",//"<h1>R-MQ-TEST Body</h1>",
			"message"=> "R-MQ GTech Test."
		]
	);
	$msg = new AMQPMessage($s);
	$channel->basic_publish($msg, '', 'emjeysSmsQ');

	echo " [x] Sms Sent.'\n";


	$channel->close();
	$connection->close();

	echo " \n[ Other tasks ] ";

}
catch(Exception $x){
	//r-mq server is down
	echo "[ R-MQ ERROR ]";
	//echo "Message:". $x->getMessage();
}




?>