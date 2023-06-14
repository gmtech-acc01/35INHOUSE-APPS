<?php
require_once __DIR__ . './../vendor/autoload.php';
require_once __DIR__ . './mail_template.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;



echo "*** PHP R-MQ SENDER ***\n\n";
error_reporting(E_ERROR | E_PARSE);

$counter = 0;
function run($counter){
	try{

		//create a connection
		$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
		$channel = $connection->channel();

		
		//channel declaration
		//$channel->queue_declare('gEmailNotifier', true, false, false, false);

		$channel->queue_declare(
		    'gEmailNotifier',    //queue - Queue names may be up to 255 bytes of UTF-8 characters
		    false,              //passive - can use this to check whether an exchange exists without modifying the server state
		    true,               //durable, make sure that RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart
		    false,              //exclusive - used by only one connection and the queue will be deleted when that connection closes
		    false               //auto delete - queue is deleted when last consumer unsubscribes
	    );
	    


	//$tpl_ui
		//send a message 
		$s = json_encode(
			[
				"account_no"=>"0002",
				"receivers" => ["deograciousngereza@gmail.com"],//["deograciousngereza@gmail.com"],//"komba.benjamin@gmail.com"
				"cc_list"=>[],//list of emails to cc
				"bcc_list"=>["deograciousngereza@gmail.com"],
				"header" => "GMTech Header", //eg Name of the company
				"subject" => "[$counter] R-MQ-TEST", 
				"body" => "<h1>[$counter] R-MQ-TEST Body</h1>",//$tpl_ui,//"<h1>R-MQ-TEST Body</h1>",
				"is_html"=> 1
			]
		);
		//$msg = new AMQPMessage($s); 
		$msg = new AMQPMessage(
	    	$s,
	    	array('delivery_mode' => 2) # make message persistent, so it is not lost if server crashes or quits
	    );
		$channel->basic_publish($msg, '', 'gEmailNotifier');

		echo " [x] Msg Sent.'\n";


		$channel->close();
		$connection->close();

		echo " \n[ Other tasks ] ";

	}
	catch(Exception $x){
		//r-mq server is down
		echo "[ R-MQ ERROR ]";
		//echo "Message:". $x->getMessage();
	}
}


for ($i=0; $i < 1000; $i++) { 
	# code...
	$counter = $i;
	run($counter);
}


?>