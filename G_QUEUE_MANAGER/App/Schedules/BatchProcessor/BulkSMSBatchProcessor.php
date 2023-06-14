<?php
namespace App\Schedules\BatchProcessor;
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
  
use App\Models\Core\SMSLogSchBatch;

class BulkSMSBatchProcessor extends Listener{

	private $batch_counts = 5;//five batches @batch 50-phone nos

	public function __construct(){
		echo "{ SMS BATCH PROCESSOR}";
	}

	public function init(){
		//load all batches which are schedulled
		$batches = SMSLogSchBatch::where('status','SCHEDULLED')->where('trial_counts','<=',2)->take($this->batch_counts)->get();
		if(sizeof($batches) == 0){ 
			$batches = SMSLogSchBatch::where('status','RETRY')->where('trial_counts','<=',2)->take($this->batch_counts)->get();
		}
		try{
		 
			//create a connection
			//$connection = new AMQPStreamConnection('35.154.93.158', 5672, 'grand', 'password');
			$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
			$channel = $connection->channel();

			
			//channel declaration
			//$channel->queue_declare('gEmailNotifier', true, false, false, false);

			$channel->queue_declare(
			    'gBulkySmsNotifier',    //queue - Queue names may be up to 255 bytes of UTF-8 characters
			    false,              //passive - can use this to check whether an exchange exists without modifying the server state
			    true,               //durable, make sure that RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart
			    false,              //exclusive - used by only one connection and the queue will be deleted when that connection closes
			    false               //auto delete - queue is deleted when last consumer unsubscribes
		    );
		    


			//loop over the batches
			for($i =0; $i < sizeof($batches);$i++){
				//update batch info
				SMSLogSchBatch::where('id',$batches[$i]->id)->update([
					'exec_at' => date('Y-m-d H:i:s'),
					'on_lock' => 1,
					'on_lock_since' =>date('Y-m-d H:i:s'),
				]);
				$s = json_encode(
					[    
						"account_no"=>$batches[$i]->sms_gqaccount_no,
						"recepients" => json_decode($batches[$i]->recepients_json,true),
						"message"=> $batches[$i]->msg,
						"sys_ref"=>$batches[$i]->batch_id,
						"sender_name"=>$batches[$i]->sender_name,
						"customer_id"=>$batches[$i]->customer_id,
						"tag"=>$batches[$i]->customer_code,
						"batch_id"=>$batches[$i]->batch_id,
						"batch_no"=>$batches[$i]->batch_no
					]
				);
				$msg = new AMQPMessage(
			    	$s,
			    	array('delivery_mode' => 2) # make message persistent, so it is not lost if server crashes or quits
			    );
				$channel->basic_publish($msg, '', 'gBulkySmsNotifier');
				echo " [x] Msg Sent.'\n";
			}//end loop

		

			$channel->close();
			$connection->close();

			echo " \n[ Other tasks ] ";

		}
		catch(\Exception $x){
			//r-mq server is down
			echo "[ R-MQ ERROR ]";
			echo "Message:". $x->getMessage();
		}
	}

}

//*INIT*/
try{
	$processor = new BulkSMSBatchProcessor();
	$processor->init();
}
catch(\Exception $ex){
	echo "\n<BATCH PROCESSOR ERROR>\n";
}
