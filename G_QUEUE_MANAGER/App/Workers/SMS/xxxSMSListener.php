<?php
namespace App\Workers\SMS;
require_once("./../../../vendor/autoload.php");


use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Base\Listener;

use App\Workers\SMS\SMSWorker;

class SMSListener extends Listener{
	
    public function __construct() {
    	echo "*** PHP R-MQ SMS-RECEIVER ***\n\n";
    }

    public function check_inputs($data){
    	$truth = true;
    	if(!isset($data['receiver_phone'])) $truth = false;
    	if(!isset($data['message'])) $truth = false;
    	return $truth;
    }

    public function xxxlisten(){
    	try{

			//create a connection
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			
			//channel declaration
			$channel->queue_declare('emjeysSmsQ', true, false, false, false);
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

				/*send an sms*/
				$worker = new SMSWorker();
				$worker->send_sms($MSG_OBJ['receiver_phone'],$MSG_OBJ['message']);
				/**/

				
				echo "\n[ SMS ] ".$MSG_OBJ['receiver_phone'] ."\n";
				$this->log(date('Y-m-d H:i:s a')." \n[ SMG ] : ".$MSG_OBJ['message']."\n");
			  	
			};

			//note: we may receive to a channel wen the producer did not publish 
			$channel->basic_consume('emjeysSmsQ', '', false, true, false, false, $callback);

			while (count($channel->callbacks)) {
			    $channel->wait();
			}

		}
		catch(Exception $x){
			$this->log(date('Y-m-d H:i:s a')." \n[ ERROR ] : ".$x->getMessage()."\n");

			//r-mq server is down
			echo "[ R-MQ ERROR ]";
			echo "Message:". $x->getMessage();
		}
    }

 
}





/*START LISTENING*/
$listener = new SMSListener;
$listener->listen();




?>