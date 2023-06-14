<?php
namespace App\Workers\FCM;
require_once("./../../../vendor/autoload.php");


use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Base\Listener;

use App\Workers\FCM\FCMWorker;

class FCMListener extends Listener{
	
    public function __construct() {
    	echo "*** PHP R-MQ FCM-RECEIVER ***\n\n";
    }

    public function check_inputs($data){
    	$truth = true;
    	if(!isset($data['receiver_token'])) $truth = false;
    	if(!isset($data['title'])) $truth = false;
    	if(!isset($data['body'])) $truth = false;
    	if(!isset($data['message'])) $truth = false;
    	return $truth;
    }

    public function listen(){
    	try{

			//create a connection
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			
			//channel declaration
			$channel->queue_declare('emjeysFCMQ', true, false, false, false);
			//echo "--".getenv('QUEUE_NAME')."\n";
			echo " [*] Waiting for messages. To exit press CTRL+C\n";
			

			//NOTE: server is sending messages in async
			$callback = function ($msg) {

				//convert msgreceived to object
				$MSG_OBJ = json_decode($msg->body,true);
				$fine = $this->check_inputs($MSG_OBJ);
				if($fine == false) {
					echo "\nINVALID INPUTS\n";
					return ;
				}

				/*send an push notification*/
				$worker = new FCMWorker();
				$worker->notify($MSG_OBJ['title'],$MSG_OBJ['body'],$MSG_OBJ['message'],$MSG_OBJ['receiver_token']);
				/**/

				
				echo "\n[ FCM ] ".$MSG_OBJ['title'] ."\n";
				$this->log(date('Y-m-d H:i:s a')." \n[ SMG ] : ".$MSG_OBJ['title']."\n");
			  	
			};

			//note: we may receive to a channel wen the producer did not publish 
			$channel->basic_consume('emjeysFCMQ', '', false, true, false, false, $callback);

			while (count($channel->callbacks)) {
			    $channel->wait();
			}

		}
		catch(\Exception $x){
			$this->log(date('Y-m-d H:i:s a')." \n[ ERROR ] : ".$x->getMessage()."\n");

			//r-mq server is down
			echo "[ R-MQ ERROR ]";
			$this->listen();//recurr
			echo "Message:". $x->getMessage();
		}
    }

 
}





/*START LISTENING*/
$listener = new FCMListener;
$listener->listen();




?>