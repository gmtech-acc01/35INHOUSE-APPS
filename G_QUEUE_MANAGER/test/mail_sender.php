<?php
require_once __DIR__ . './../vendor/autoload.php';
require_once __DIR__ . './mail_template.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$loader = new \Twig\Loader\FilesystemLoader('./twig');
$twig = new \Twig\Environment($loader, [
   // 'cache' => './twig/cache',
]);


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
	$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
	//$connection = new AMQPStreamConnection('35.154.93.158', 5672, 'grand', 'password');
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
    


	//twig
	//$twig->render('index.html', ['the' => 'variables', 'go' => 'here']);

//$tpl_ui
	//send a message 
	$sys_ref_token = getSysRefToken();
	$s = json_encode(
		[
			"account_no"=>"GMTECHINFO",//110011  0001 0000001 PAXREPORTS01 GMTECHINFO FARISDEV01
			"receivers" => ["deograciousngereza@gmail.com"],// ["deograciousngereza@gmail.com"],//"komba.benjamin@gmail.com"
			"cc_list"=>[],//list of emails to cc
			"bcc_list"=>[], 
			"header" => "GMTech", //eg Name of the company  
			"subject" => "<GMTECH Sample Uber receipts>", 
			"body" => $twig->render('sample_uber_receipt.html', ['DATA'=>"DATA COMES HERE",'the' => 'variables', 'go' => 'here']),//$tpl_ui,//"<h1>R-MQ-TEST Body</h1>",
			"is_html"=> 1, 

			"sys_ref" => $sys_ref_token,
			"app_ref" => getSysRefToken(),//optional
		]
	);
	//$msg = new AMQPMessage($s);
	$msg = new AMQPMessage( 
    	$s,
    	array('delivery_mode' => 2) # make message persistent, so it is not lost if server crashes or quits
    );
	$channel->basic_publish($msg, '', 'gEmailNotifier');

	echo " [x] Msg Sent.[$sys_ref_token]'\n";


	$channel->close();
	$connection->close();

	echo " \n[ Other tasks ] ";

}
catch(Exception $x){
	//r-mq server is down
	echo "[ R-MQ ERROR ]";
	echo "Message:". $x->getMessage();
}




?>