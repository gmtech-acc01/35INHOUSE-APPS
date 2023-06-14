<?php
require_once __DIR__ . './../vendor/autoload.php';
require_once __DIR__ . './mail_template.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



echo "*** PHP R-MQ SENDER ***\n\n";
error_reporting(E_ERROR | E_PARSE);


try{

	//create a connection
	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
	$channel = $connection->channel();

	
	//channel declaration
	$channel->queue_declare('emjeysFCMQ', true, false, false, false);


//$tpl_ui
	//send a message 
	$s = json_encode(
		[
			"receiver_token" => "drP4qrknIos:APA91bFDnSGKJi3rVvgOe7jvHZDmZvFc_gLbcFEzHvft6NgmqJncQ2_04xsDzUjRIE9OY3g0CLSM0Wl6Evy81MDLDsKN0_qo6LQHbWY9pt7Q1NtvRvgRLiM076oRSbuIq1QXZuKgRFRL",
			"title" => "R-MQ-FCM-TEST", 
			"body" => "FCM notification test.",//"<h1>R-MQ-TEST Body</h1>",
			"message"=> ["src"=>"Grand Technologies LTD."]
		]
	);
	$msg = new AMQPMessage($s);
	$channel->basic_publish($msg, '', 'emjeysFCMQ');

	echo " [x] Notification Sent.'\n";


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