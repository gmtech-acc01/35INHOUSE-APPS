var axios = require('axios');
var mysql      = require('mysql');
var $SETTINGS = require('./../settings.json');
var amqp = require('amqplib/callback_api');


console.log("*** NODE IP RECORDER WORKER *******\n");



/*return base ip gateway url*/
var get_custom_gateway_url = function(ip){
	switch($SETTINGS.IP_GATEWAY){
		case 'ip-api.com' : return "http://ip-api.com/json/"+ip+"?fields=782335&lang=en";break;
		default : return "http://ip-api.com/json/"+ip+"?fields=782335&lang=en";break;
	}
};

/*standardize results from the given api*/
var get_standard_results = function($data){
	$res = {};
	switch($SETTINGS.IP_GATEWAY){
		case 'ip-api.com' :
			$res = {
				"country" : $data.country,
				"city" : $data.city,
				"country_code" : $data.countryCode,
				"district" : $data.district,
				"isp" : $data.isp,
				"proxy" : $data.proxy == true ? "YES" : "NO",
				"latitude" : $data.lat,
				"longitude" : $data.lon,
				"region" : $data.regionName,
				"timezone" : $data.timezone
			};
			break;
	}
	return $res;
};


/*r-mq sender notify map finder to find the details of given code/log_id ,lat,lon*/
var notify_map_finder = function($log_id,$lat,$lon){
	amqp.connect('amqp://localhost', function(err, conn) {
	  conn.createChannel(function(err, ch) {
	    var q = 'gProfileMapFinderQ';
	    var msg = {'code' : $log_id,'latitude':$lat,'longitude':$lon};
	    msg = JSON.stringify(msg);

	    ch.assertQueue(q, {durable: true});
	    ch.sendToQueue(q, Buffer.from(msg));
	    console.log(" [x] Sent %s", msg);
	  });
	  setTimeout(function() { conn.close(); process.exit(0) }, 500);
	}); 
};


/*save to the database*/
var save2DB = function($log_id,$data){
	console.log("-- save to DB --");
	console.log($data);
	var connection = mysql.createConnection({
	  host     : $SETTINGS.DB_CONN.host,
	  user     : $SETTINGS.DB_CONN.user,
	  password : $SETTINGS.DB_CONN.password,
	  database : $SETTINGS.DB_CONN.database
	});
	 
	connection.connect();
	var q = "UPDATE visitors_list SET ";
	q += "country = '" + $data.country + "',";
	q += "city = '" + $data.city + "',";
	q += "country_code = '" + $data.country_code + "',";
	q += "district = '" + $data.district + "',";
	q += "isp = '" + $data.isp + "',";
	q += "proxy = '" + $data.proxy + "',";
	q += "latitude = " + $data.latitude + ",";
	q += "longitude = " + $data.longitude + ",";
	q += "timezone = '" + $data.timezone + "',";
	q += "region = '" + $data.region + "'";
	q += " WHERE code = '" + $log_id + "';";

	connection.query(q, function (error, results, fields) {
	  if (error){
	  		console.log("[ DB ERROR ]");
	  		return;
	  }
	  //console.log('The solution is: ', results);
	  notify_map_finder($log_id,$data.latitude,$data.longitude);
	});
	 
	connection.end();
};




/*
	- GRAP THE IP INFOS FROM THE GATE WAY 
	- STANDARDIZE THE RESULTS
	- SAVE TO LOCAL MYSQL DB
*/
var get_ip_info = function($log_id,$ip_address){
	if($ip_address == "" || $ip_address == null || undefined) return;

	var conn = axios.get(get_custom_gateway_url($ip_address));
	  conn.then(function (response) {
	  	console.log("FINE:");
	  	var data = get_standard_results(response.data);
	    save2DB($log_id,data);
	  })
	  .catch(function (error) {
	    //console.log(error);
	    console.log("--ERROR--");
	  });
};

get_ip_info("a4bcd5e-f4g5hi-2jkl3m3-nopq234","41.75.222.200");









