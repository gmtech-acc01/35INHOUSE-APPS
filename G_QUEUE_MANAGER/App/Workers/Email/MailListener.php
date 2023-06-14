<?php
namespace App\Workers\Email;


/*BOOTING*/
require_once("./../../../vendor/autoload.php");
use App\Models\Core\Database;
//Initialize Illuminate Database Connection
new Database("grand_queue_manager");



use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Base\Listener;


use App\Workers\Email\MailWorker;
use App\Models\Core\EmailLog;

class MailListener extends Listener{
	
    public function __construct() {	
    	$this->load_env();
    	echo "*** PHP R-MQ MAIL RECEIVER ***\n\n";
    	echo "--".getenv('Q_NAME')."--\n";
    }

    public function log($m,$f='email.txt'){
    	parent::log($m,$f);
    }

    private function load_env(){
    	//load env loader
    	$dotenv = \Dotenv\Dotenv::create(__DIR__);
		$dotenv->load();
    }

    public function check_inputs($data){
    	$truth = true;
    	if(!isset($data['account_no'])) $truth = false;
    	if(!isset($data['receivers'])) $truth = false;
    	if(!isset($data['cc_list'])) $truth = false;
    	if(!isset($data['bcc_list'])) $truth = false;
    	if(!isset($data['header'])) $truth = false;
    	if(!isset($data['subject'])) $truth = false;
    	if(!isset($data['body'])) $truth = false;
    	if(!isset($data['is_html'])) $truth = false;
    	if(!isset($data['sys_ref'])) $truth = false;
    	return $truth;
    }

    public function listen(){ 
    	try{

			//create a connection
			$connection = new AMQPStreamConnection(getenv('HOST'), getenv('PORT'), getenv('USER'), getenv('PASSWORD'));
			$channel = $connection->channel();

			
			//channel declaration
			$channel->queue_declare(getenv('Q_NAME'), getenv('Q_PASSIVE_OPT'), getenv('Q_DURABLE_OPT'), getenv('Q_EXCLUSIVE_OPT'), getenv('Q_AUTODELETE_OPT'));
			//echo "--".getenv('QUEUE_NAME')."\n";
			echo "\n [*] Waiting for messages. To exit press CTRL+C\n";


			$channel->basic_qos(
	            null,//prefetch size - prefetch window size in octets, null meaning "no specific limit"
	            1,//prefetch count - prefetch window in terms of whole messages
	            null//global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
            );
			 

			//note: we may receive to a channel wen the producer did not publish 
			//$channel->basic_consume(getenv('Q_NAME'), '', false, true, false, false, $callback);
			$channel->basic_consume(
				getenv('Q_NAME'),
				'gmtech-'.getmypid(),//consumer tag - Identifier for the consumer, valid within the current channel. just string
	            false,//no local - TRUE: the server will not send messages to the connection that published them
	            false,//no ack, false - acks turned on, true - off.  send a proper acknowledgment from the worker, once we're done with a task
	            false,//exclusive - queues may only be accessed by the current connection
	            false,//no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
	            array($this, 'process') //callback
			); 

			while (count($channel->callbacks)) {
				echo ("\nWaiting for incoming messages\n");
			    $channel->wait();
			}

			$channel->close();
        	$connection->close();
		}
		catch(\Exception $x){
			$this->log(date('Y-m-d H:i:s a')." \n[ ERROR ] : ".$x->getMessage()."\n");

			//r-mq server is down
			echo "[ R-MQ ERROR ]";
			//$this->listen();//recurr
			//echo "Message:". $x->getMessage();
		}
    }


     /**
     * process received request
     * 
     * @param AMQPMessage $msg
     */ 
    public function process(AMQPMessage $msg)
    {

    	//convert msgreceived to object
		$MSG_OBJ = json_decode($msg->body,true);

		if(!isset($MSG_OBJ['sys_ref'])){
			echo "REF-NOT SET!!!";
			$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			return;
		};
		
		$oldLog = EmailLog::where('sys_ref',$MSG_OBJ['sys_ref'])->first();//
		

		if($oldLog != null){
			//simply update the trial counts
			EmailLog::where("sys_ref",$MSG_OBJ['sys_ref'])->update([
				"updated_at" => date('Y-m-d H:i:s'),
				"status" => "PENDING",
				"pid"=> getmypid(),
				"trial_counts"=>($oldLog->trial_counts + 1),
				"last_trial_at"=> date('Y-m-d H:i:s'),
			]);
		}
		else{
			/*record the entry*/
	    	$log_entry = new EmailLog();
	    	$log_entry->sys_ref = $MSG_OBJ['sys_ref'];
	    	$log_entry->app_ref = isset($MSG_OBJ['app_ref']) ? $MSG_OBJ['app_ref'] : $MSG_OBJ['sys_ref'];
	    	//$log_entry->api_ref = $msg->sys_ref;
	    	$log_entry->account_no = $MSG_OBJ['account_no'];
	    	$log_entry->json_receivers = json_encode($MSG_OBJ['receivers'],true);
	    	$log_entry->json_cc_list = json_encode($MSG_OBJ['cc_list'],true);
	    	$log_entry->json_bcc_list = json_encode($MSG_OBJ['bcc_list'],true);
	    	$log_entry->header = $MSG_OBJ['header'];
	    	$log_entry->subject = $MSG_OBJ['subject'];
	    	$log_entry->body = $MSG_OBJ['body'];
	    	$log_entry->is_html = $MSG_OBJ['is_html'];
	    	$log_entry->created_date = date('Y-m-d');
	    	$log_entry->created_at = date('Y-m-d H:i:s');
	    	$log_entry->updated_at = date('Y-m-d H:i:s');
	    	//$log_entry->completed_at = date('Y-m-d');
	    	//$log_entry->approximate_time_in_sec = $msg->sys_ref;
	    	$log_entry->status = "PENDING";
	    	//$log_entry->trial_counts = $msg->sys_ref;
	    	//$log_entry->last_trial_at = date('Y-m-d H:i:s');
	    	$log_entry->pid = getmypid();
	    	//$log_entry->response_type = $msg->sys_ref;
	    	//$log_entry->response_info = $msg->sys_ref;
	    	//$log_entry->error_info = $msg->sys_ref;
	    	$log_entry->save();

	    	$oldLog = $log_entry;
		}
    	

        
		$fine = $this->check_inputs($MSG_OBJ);
		if($fine == false) { 
			echo "\nINVALID INPUTS\n";
			//delivery note to q manager ::TODO-add to fail list
			$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			EmailLog::where("sys_ref",$MSG_OBJ['sys_ref'])->update([
				"updated_at" => date('Y-m-d H:i:s'),
				"status" => "INVALID-INPUTS"
			]);
			return ;
		}

		//$in_reply_to_email
		$in_reply_to_email = isset($MSG_OBJ['in_reply_to_email']) ? $MSG_OBJ['in_reply_to_email'] : "";
		$in_reply_to_title = isset($MSG_OBJ['in_reply_to_title']) ? $MSG_OBJ['in_reply_to_title'] : "";

		/*send an email*/ 
		$worker = new MailWorker();
		$module_results = $worker->send($MSG_OBJ['account_no'],$MSG_OBJ['receivers'],$MSG_OBJ['cc_list'],$MSG_OBJ['bcc_list'],$in_reply_to_email,$in_reply_to_title,$MSG_OBJ['header'],$MSG_OBJ['subject'],$MSG_OBJ['body'],$MSG_OBJ['is_html']);
		
		EmailLog::where("sys_ref",$MSG_OBJ['sys_ref'])->update([
				"updated_at" => date('Y-m-d H:i:s'),
				"status" => $module_results["response_type"],
				"response_type" => $module_results["response_type"],
                "response_info" => $module_results["response_info"], 
                "error_info"=>$module_results["error_info"],
                "completed_at" =>date('Y-m-d H:i:s'),
                "status"=>$module_results["response_type"],
                "approximate_time_in_sec"=> strtotime(date('Y-m-d H:i:s')) - strtotime($oldLog->created_at)
			]);
		/**/
 

		echo "\n[ EMAIL ] ".$MSG_OBJ['subject'] ."\n"; 
		$this->log('['.getmypid().'-'.getenv('Q_NAME').'-'.$MSG_OBJ['account_no'].'] '. date('Y-m-d H:i:s')." \n[ SMG ] : ".$MSG_OBJ['subject']."","email.txt");

        
        /**
         * If a consumer dies without sending an acknowledgement the AMQP broker 
         * will redeliver it to another consumer or, if none are available at the 
         * time, the broker will wait until at least one consumer is registered 
         * for the same queue before attempting redelivery
         */ 
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }

 
}




/*START LISTENING*/
$listener = new MailListener;//$listener->log('shutdown','email.txt');
try{
	echo "listener with ID: ".getmypid().' running...';
	$listener->log("RUN::[ ".getmypid()." ]",'email.txt');
	$listener->listen();
}catch(\Exception $e){//SystemExit
	echo 'Script executed with success', PHP_EOL;
	$listener->log("SHUTDOWN::[ ".getmypid()." ]",'email.txt');
}





?>