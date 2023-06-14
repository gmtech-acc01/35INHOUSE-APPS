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
	//$channel->queue_declare('gEmailNotifier', true, false, false, false);

	$channel->queue_declare(
	    'gOSNotifier',    //queue - Queue names may be up to 255 bytes of UTF-8 characters
	    false,              //passive - can use this to check whether an exchange exists without modifying the server state
	    true,               //durable, make sure that RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart
	    false,              //exclusive - used by only one connection and the queue will be deleted when that connection closes
	    false               //auto delete - queue is deleted when last consumer unsubscribes
    );
    

	//send a message 
	$s = json_encode(
		[ 
			//"account_no"=>"0001",//"GMNXM01",//"MOVESMS01",
			"group_name" => "GROUP",//GROUP/ACCOUNT/PLAYERorDEVICE
			"content_recepient_list" => ["GT-RECEPTION","HQ-RECEPTION"],//GT-RECEPTION 0001
			"subject" => "GMTECH LTD",  
			"body" => "Grand Master", 
			"url" => "http://millardayo.com/wp-content/uploads/2019/08/Screen-Shot-2019-08-09-at-2.24.56-PM-360x220.png",
			"img" => "http://millardayo.com/wp-content/uploads/2019/08/Screen-Shot-2019-08-09-at-2.24.56-PM-360x220.png",
			"icon"=>"http://millardayo.com/wp-content/uploads/2019/08/Screen-Shot-2019-08-09-at-2.24.56-PM-360x220.png"
		]  
	);
	//$msg = new AMQPMessage($s);
	$msg = new AMQPMessage(
    	$s,
    	array('delivery_mode' => 2) # make message persistent, so it is not lost if server crashes or quits
    );
	$channel->basic_publish($msg, '', 'gOSNotifier');

	echo " [x] Msg Sent.'\n";


	$channel->close();
	$connection->close();
 
	echo " \n[ Other tasks ] ";

}
catch(\Exception $x){
	//r-mq server is down
	echo "[ R-MQ ERROR ]";
	//echo "Message:". $x->getMessage();
}




?>