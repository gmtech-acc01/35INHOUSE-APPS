<?php
namespace App\Workers\FCM;

class FCMWorker{

	private $server_key = "AAAAYpGRsoE:APA91bGvYMl6xb5AmEgrEhNGLU-A6t6a-k7aUVBNNBToJhygdUGkTB5-Yjb8HWFDLK1nbxwsp6JSM6WRMDEoxGUKCGIINBmoHJ9jyjjy1TZDSJ7YjlzIPCeYoILoFDjnXfsgkzsMx1KU";

	private $fcm_actived = true;	

	public function __construct(){
		
	}

	

	public function notify($title,$body,$message, $id) {

		if($this->fcm_actived == false) return "";


	    $url = 'https://fcm.googleapis.com/fcm/send';

	    $fields = array (
	    	"to" => $id,
	    	"notification" => [
	    		"title" => $title,
		      	"body" => $body,
		      	"sound" => "default",
		     	"badge" =>  "100"
		     ],
		     "data" => $message
	    );
	   
	    $fields = json_encode ( $fields );

	    $headers = array (
	            'Authorization: key=' . "AAAAYpGRsoE:APA91bGvYMl6xb5AmEgrEhNGLU-A6t6a-k7aUVBNNBToJhygdUGkTB5-Yjb8HWFDLK1nbxwsp6JSM6WRMDEoxGUKCGIINBmoHJ9jyjjy1TZDSJ7YjlzIPCeYoILoFDjnXfsgkzsMx1KU",
	            'Content-Type: application/json'
	    );

	    $ch = curl_init ();
	    curl_setopt ( $ch, CURLOPT_URL, $url );
	    curl_setopt ( $ch, CURLOPT_POST, true );
	    curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
	    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
	    
	    $result = curl_exec ( $ch );
	    $result_obj = json_decode($result,true);

	    
	    curl_close ( $ch );

	    //echo "success=>".$result_obj['success']."<br>";
	    //echo $result;
	}





}



