<?php
namespace App\Workers\Email;
//require_once("./../../../vendor/autoload.php");

/*BOOTING*/ 
require_once("./../../../vendor/autoload.php");
use App\Models\Core\Database;
//Initialize Illuminate Database Connection
new Database("grand_queue_manager");




use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Base\Listener;

use App\Workers\SMS\SMSWorker;
use App\Models\Core\SMSLog;

use App\Models\Core\AccountSms;//for viewing account details

use App\Models\Core\API\CustomerAccount;
use App\Models\Core\API\SMSOutgoing;
  
class SMSListener extends Listener{
	
    public function __construct() {	
    	$this->load_env();
    	echo "*** PHP R-MQ SMS RECEIVER ***\n\n";
    	echo "--".getenv('Q_NAME')."--\n";
    }


    //mark the parent syst sms outgoin with status
    public function updateInternalCallBack($sys_ref,$module_results){

    	try{
    		//TODO:: use curl post/get to update the mother account
	    	
	    	
	    	$smsOutgoing = SMSOutgoing::where('sys_ref',$sys_ref)->first();

	    	if($smsOutgoing == null) return;
	    	$customer_id = $smsOutgoing->customer_id;//getting customer account
	    	$smsOutgoing->sent_at = date('Y-m-d H:i:s');
	    	$smsOutgoing->sent_date = date('Y-m-d');
	    	$smsOutgoing->sent_time = date('H:i:s');
	    	$smsOutgoing->status = $module_results["response_type"];
	    	$smsOutgoing->response_type = $module_results["response_type"];
	    	$smsOutgoing->response_info = $module_results["response_info"];
	    	$smsOutgoing->error_info = $module_results["error_info"];
	    	$smsOutgoing->update();

	    	//check if not sent increase the quota
	    	
	    	if($module_results["response_type"] != 'SENT'){
	    		//$customer_id add sms_quota to a customer _id
	    		$acc = CustomerAccount::find($customer_id);
	    		if($acc == null) return;
	    		$acc->sms_quota = $acc->sms_quota + ($smsOutgoing->sms_units);
	    		$acc->update();
	    	}

    	}catch(\Exception $ex){
    		//
    		echo "Exception:on update the receipt!!";
    	}
    }



    private function getSysRefToken($length = 4){

	    $token = "";
	     //$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	     //$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	     $codeAlphabet.= "0123456789";
	     $max = strlen($codeAlphabet); // edited

	    for ($i=0; $i < $length; $i++) {
	        $token .= $codeAlphabet[random_int(0, $max-1)];

	    }

	    return date('dmYHis').'.'.$token;
	}


    public function log($m,$f='sms.txt'){
    	parent::log($m,$f);
    }

    private function load_env(){
    	//load env loader
    	$dotenv = \Dotenv\Dotenv::create(__DIR__);
		$dotenv->load();
    }
    public function totalSMSCountInBody($body){

    	$one_sms_size = 160;
    	$sms_len = strlen($body);

    	if($sms_len <= $one_sms_size){
    		return 1;//one sms
    	} 

    	$res = $sms_len / 160;
    	return 1 + round($res);//1 was default
    }

    public function check_inputs($data){
    	//var_dump($data) ;
    	$truth = true;
    	
    	if(!isset($data['receiver_phone'])) $truth = false;
    	if(!isset($data['account_no'])) $truth = false;
    	if(!isset($data['message'])) $truth = false;
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
				echo ("\nWaiting for incoming messages");
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
		if(!isset($MSG_OBJ['sys_ref'])) $MSG_OBJ['sys_ref'] = $this->getSysRefToken();

		$account = AccountSms::where('acc_no',$MSG_OBJ['account_no'])->first();//TODO::remove here
		$_sender_name = "INFO";
		$oldLog = SMSLog::where('sys_ref',$MSG_OBJ['sys_ref'])->first();//
		if($oldLog != null){
			//simply update the trial counts
			SMSLog::where("sys_ref",$MSG_OBJ['sys_ref'])->update([
				"updated_at" => date('Y-m-d H:i:s'),
				"status" => "PENDING",
				"pid"=> getmypid(),
				"trial_counts"=>($oldLog->trial_counts + 1),
				"last_trial_at"=> date('Y-m-d H:i:s'),
			]);
		}
		else{

			$recList = [];
			array_push($recList, $MSG_OBJ['receiver_phone']);

			/*fix sender name*/
			$account == null ? '' : $account->api_sender_name;
			
			if($account != null){
				if(isset($MSG_OBJ['sender_name'])){
					if($MSG_OBJ['sender_name'] == ""){
						$_sender_name = "INFO";
					}else{
						$_sender_name = $MSG_OBJ['sender_name'];
					}
				}else{
					$_sender_name = "INFO";
				}
			}
			
			/*record the entry*/
	    	$log_entry = new SMSLog();
	    	$log_entry->sys_ref = $MSG_OBJ['sys_ref'];
	    	$log_entry->app_ref = isset($MSG_OBJ['app_ref']) ? $MSG_OBJ['app_ref'] : $MSG_OBJ['sys_ref'];
	    	//$log_entry->api_ref = $msg->sys_ref;
	    	$log_entry->account_no = $MSG_OBJ['account_no'];
	    	$log_entry->json_receivers = json_encode($recList,true);
	    	$log_entry->sender_name = $_sender_name;//$account == null ? '' : $account->api_sender_name;
	    	$log_entry->sms_text_length = strlen($MSG_OBJ['message']);
	    	$log_entry->sms = $MSG_OBJ['message']; 
	    	$log_entry->sms_counts = $this->totalSMSCountInBody($MSG_OBJ['message']);
	    	$log_entry->tag = isset($MSG_OBJ['tag']) ? $MSG_OBJ['tag'] : '';
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
			SMSLog::where("sys_ref",$MSG_OBJ['sys_ref'])->update([
				"updated_at" => date('Y-m-d H:i:s'),
				"status" => "INVALID-INPUTS"
			]); 
			return ;
		}


		/*send sms*/
		$worker = new SMSWorker(); 
		$module_results = $worker->send_sms($MSG_OBJ['account_no'],$MSG_OBJ['receiver_phone'],$MSG_OBJ['message'],$_sender_name);
		/**/
		SMSLog::where("sys_ref",$MSG_OBJ['sys_ref'])->update([ 
				"updated_at" => date('Y-m-d H:i:s'),
				"status" => $module_results["response_type"],
				"response_type" => $module_results["response_type"],
                "response_info" => $module_results["response_info"], 
                "error_info"=>$module_results["error_info"],
                "completed_at" =>date('Y-m-d H:i:s'),
                "status"=>$module_results["response_type"],
                "approximate_time_in_sec"=> strtotime(date('Y-m-d H:i:s')) - strtotime($oldLog->created_at)
			]);

		//tell the parent app(api-sys)
		$this->updateInternalCallBack($MSG_OBJ['sys_ref'],$module_results);

		//echo "\n[ SMS ] ".$MSG_OBJ['subject'] ."\n";
		$this->log('['.getmypid().'-'.getenv('Q_NAME').'-'.$MSG_OBJ['account_no'].'] '. date('Y-m-d H:i:s')." \n[ SMS ] : ".$MSG_OBJ['receiver_phone']."","sms.txt");

        
        /**
         * If a consumer dies without sending an acknowledgement the AMQP broker 
         * will redeliver it to another consumer or, if none are available at the 
         * time, the broker will wait until at least one consumer is registered 
         * for the same queue before attempting redelivery
         */ 
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }


    public function xprocess(AMQPMessage $msg)
    {

        //convert msgreceived to object
		$MSG_OBJ = json_decode($msg->body,true);
		$fine = $this->check_inputs($MSG_OBJ);
		if($fine == false) { 
			echo "\nINVALID INPUTS\n";
			//delivery note to q manager ::TODO-add to fail list
			$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
			return ;
		}

		

		/*send sms*/
		$worker = new SMSWorker();
		$worker->send_sms($MSG_OBJ['account_no'],$MSG_OBJ['receiver_phone'],$MSG_OBJ['message']);
		/**/


		//echo "\n[ SMS ] ".$MSG_OBJ['subject'] ."\n";
		$this->log('['.getmypid().'-'.getenv('Q_NAME').'-'.$MSG_OBJ['account_no'].'] '. date('Y-m-d H:i:s')." \n[ SMS ] : ".$MSG_OBJ['receiver_phone']."","sms.txt");

        
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
$listener = new SMSListener;//$listener->log('shutdown','email.txt');
try{
	echo "listener with ID: ".getmypid().' running...';
	$listener->log("RUN::[ ".getmypid()." ]",'sms.txt');
	$listener->listen();
}catch(\Exception $e){//SystemExit
	echo 'Script executed with success', PHP_EOL;
	$listener->log("SHUTDOWN::[ ".getmypid()." ]",'sms.txt');
}





?>