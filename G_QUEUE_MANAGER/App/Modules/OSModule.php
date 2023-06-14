<?php
namespace App\Modules; 


class OSModule{


    public function __construct() {
    }


 
    /*
		push to multi players.
    */
    public function push_to_players($app_id,$rest_api_key,$subject,$body,$players_list,$img_url,$icon_url,$url){

    	$subject = $subject;
    	$img_url = $img_url;
    	$icon_url = $icon_url;
    	$url = $url;

    	echo "\nPUSHING...\n".$app_id."\n".$rest_api_key."\n".json_encode($players_list)."\n";

		$content = array(
        	"en" => $body
        ); 
	    $fields = array(
	        'app_id' => $app_id,
	        'data' => array("app" => "g-note"),
	        'headings' => [
	        	"en" => $subject
	        ],
	        'include_player_ids' => $players_list,
	        'large_icon' =>$img_url,
	        'contents' => $content,
	        'url'=> $url,
	    	'chrome_web_icon'=> $icon_url,      
	    	'chrome_web_image'=> $img_url
	    );

	    $fields = json_encode($fields);
	
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8','Authorization: Basic '.$rest_api_key));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($ch, CURLOPT_HEADER, FALSE);
	    curl_setopt($ch, CURLOPT_POST, TRUE);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    

	    $os_response = curl_exec($ch);
	    curl_close($ch);

		return [ 
			"status"=>"OK",
			"msg"=>"MSG-SENT.",
			"os_response"=>$os_response
		];
    }


};

    


/*
$os_mod = new OSModule();
$os_mod->push_to_players(
	"24f9aee8-822e-4803-b414-d330a64d1bc3",//app_id
	"MTQ5MGM5MDQtMjc5Ni00OWU0LThjZjQtZTBiY2U1MTRiN2E4",//rest_api_key
	"Subject",//subject
	"Body",//body
	["83183f82-1a64-4c1e-a726-68fd32574faf"],//players_list
	"",//img_url
	"",//icon_url
	""//url
);
*/


?>