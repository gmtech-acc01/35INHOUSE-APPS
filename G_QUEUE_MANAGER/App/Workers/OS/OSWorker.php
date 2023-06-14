<?php
namespace App\Workers\OS;

use App\Modules\OSModule;

use App\Base\Worker;

/**/
use App\Models\OS\OS;
use App\Models\OS\OSCategory;
use App\Models\OS\OSPlayer;
use App\Models\OS\OSAccount;
use App\Models\OS\OSAccountHasCategory;



/*BOOTING*/
require_once("./../../../vendor/autoload.php");
use App\Models\Core\Database;
//Initialize Illuminate Database Connection
new Database("grand_queue_manager");




class OSWorker extends Worker{


	public function __construct() {
    }

    public function log($m,$f='one_signal.txt'){
        parent::log($m,$f);
    }

     /* 
        push to a single or multi players 
        i.e: [abc-5shah,77shd8-sajs7n-sabsb]
        push to all given player ids
        TODO::
    */
   

    /* 
        push to a single or multi account nos
        i.e: [001,002]
        find all players for each account
    */
    public function push_to_accounts($content_recepient_list,$subject,$body,$img_url,$icon_url,$url){

        //check if we have atleast one account on the given list
        if(sizeof($content_recepient_list) == 0) {
            echo "\nZERO-ACCOUNTS DETECTED\n";
            $this->log("ZERO-ACCOUNTS DETECTED");
            return json_encode(["status"=>"ERROR","msg"=>"ZERO-ACCOUNTS DETECTED"]);
        } 

        //only one receiver account can help us to read the overal customer root account
        $receiver_account = OSAccount::where("acc_no",$content_recepient_list[0])->first();//first account [001,002]
        if($receiver_account == null) {
            echo "\nINVALID RECEIVER-ACCOUNT\n";
            $this->log("INVALID RECEIVER-ACCOUNT");
            return json_encode(["status"=>"ERROR","msg"=>"INVALID RECEIVER-ACCOUNT"]);
        }

        //load info about the one signal account
        $osCustomer = OS::where("name",$receiver_account->customer_code)->first();
        if($osCustomer == null) {
            echo "\nINVALID CUSTOMER-INFO\n";
            $this->log("INVALID CUSTOMER-INFO");
            return json_encode(["status"=>"ERROR","msg"=>"INVALID CUSTOMER-INFO"]);
        }

        //iterate over all the given account - find the players
        $preparedPlayers = [];
        for ($i=0; $i < sizeof($content_recepient_list); $i++) { 
            //@each receiver account
            $receiver_account = OSAccount::where("acc_no",$content_recepient_list[$i])->first();
            if($receiver_account != null){
                $raw_acc_players = OSPlayer::where("account_id",$receiver_account->id)->where("subscribe",1)->get();
                for($j= 0; $j < sizeof($raw_acc_players);$j++){
                    //check if player exists on list before add
                    $found = 0;
                    for($k = 0;$k < sizeof($preparedPlayers);$k++){
                        if($raw_acc_players[$j]["player_id"] == $preparedPlayers[$k]) $found = 1;
                    }
                    if($found == 0) array_push($preparedPlayers, $raw_acc_players[$j]["player_id"]);
                }
            }
        }

        //check if players are available
        if(sizeof($preparedPlayers) == 0) {
            echo "\nNo devices available!\n";
            $this->log("No devices available!");
            return json_encode(["status"=>"OK","msg"=>"No devices available!"]);
        }
       // echo "PUSH";
        $os_mod = new OSModule();
        $os_res = $os_mod->push_to_players(
            $osCustomer->app_id,//app_id
            $osCustomer->rest_api_key,//rest_api_key
            $subject,//subject 
            $body,//body
            $preparedPlayers,//players_list
            $img_url,//img_url
            $icon_url,//icon_url
            $url == "" ? $osCustomer->url : $url//url
        );
        echo "\nOS-RESPONCE:: \n".$os_res["os_response"];
    }
    //


     /*
        push to a single or multi group
        i.e: ["group1","group2"] group has many accounts
        accounts has many devices
    */
    public function push_to_groups($content_recepient_list,$subject,$body,$img_url,$icon_url,$url){ 

        //content_recepient_list is a group list 

        //check if we have atleast one account on the given list
        if(sizeof($content_recepient_list) == 0) {
            echo "\nZERO-GROUPS DETECTED\n";
            $this->log("ZERO-GROUPS DETECTED");
            return json_encode(["status"=>"ERROR","msg"=>"ZERO-GROUPS DETECTED"]);
        } 

        //load accounts for each group
        $rec_acc_nos = [];
        for ($i=0; $i < sizeof($content_recepient_list); $i++) { 
            //
            $catObj = OSCategory::where("name",$content_recepient_list[$i])->where("deleted",0)->first(); 

            if($catObj == null) continue;

            $accIds = OSAccountHasCategory::where("cat_id",$catObj->id)->with("account")->get();
            if(sizeof($accIds) == 0) continue;
            for($j = 0; $j < sizeof($accIds);$j++){
                $id_found = 0;
                for($k = 0; $k < sizeof($rec_acc_nos);$k++){
                    if($rec_acc_nos[$k] == $accIds[$j]->account["acc_no"]){
                        $id_found = 1;//break k loop
                    }
                }
                if($id_found == 0) array_push($rec_acc_nos, $accIds[$j]->account["acc_no"]);
            }
        }

        //echo "\n==> ".json_encode($rec_acc_nos);

        /*from here is the same as the message for the account*/
        $content_recepient_list = $rec_acc_nos;
        return $this->push_to_accounts($content_recepient_list,$subject,$body,$img_url,$icon_url,$url);
    }
    //





    
}





