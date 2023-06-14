<?php


$phone = "0 788-44-90-30";

function modify($phone){
	echo "Phone:".$phone."\n";
	$phone = str_replace('-', '', $phone);//remove dashes
	$phone = preg_replace('/\s+/', '', $phone);//remove white space
	echo "Phone:".$phone."\n";

	if(substr($phone, 0,4) == "+255"){
		echo "+255\n";
		$phone = "255".substr($phone, 4,strlen($phone) - 4);
		echo "Phone:".$phone."\n";
	}
	else if(substr($phone, 0,3) == "255"){
		echo "255\n";
		echo "Phone:".$phone."\n";
	}
	else if(substr($phone, 0,1) == "0"){
		echo "0\n";
		$phone = "255".substr($phone, 1,strlen($phone) - 1);
		echo "Phone:".$phone."\n";
	}

}
modify($phone);


?>