var mysql      = require('mysql');
var axios = require('axios');
var $SETTINGS = require('./../settings.json');
var MAIL_TPL = require('./mail_tpl.js');
var amqp = require('amqplib/callback_api');


console.log("*** NODE GEO-FINDER WORKER *******\n");


var notifyViaEmail = function($data){
	amqp.connect('amqp://localhost', function(err, conn) {
	  conn.createChannel(function(err, ch) {
	    var q = 'emjeysMailerQ';//TODO
		var msg = $data;
	    msg = JSON.stringify(msg);

	    ch.assertQueue(q, {durable: true});
	    ch.sendToQueue(q, Buffer.from(msg));
	    console.log(" [x] Sent %s", msg);
	  });
	  setTimeout(function() { conn.close(); process.exit(0) }, 500);
	}); 
};

//
var checkIfWeCanNotify = function($log_id){
	var connection = mysql.createConnection({
	  host     : $SETTINGS.DB_CONN.host,
	  user     : $SETTINGS.DB_CONN.user,
	  password : $SETTINGS.DB_CONN.password,
	  database : $SETTINGS.DB_CONN.database
	});
	 
	connection.connect();
	var q = "SELECT * FROM visitors_list WHERE code = '"+$log_id+"' order by id DESC LIMIT 1 ;";

	//console.log("->" + q);
	//return;
	connection.query(q, function (error, results, fields) {
	  if (error){
	  		console.log("[ DB ERROR ]" + error);
	  		return;
	  }
	  if(results.length != 1) return;
	  $res = results[0];
	  $past_time = $res.time;
	  $current_time = Math.floor(Date.now() / 1000);
	  $diff_in_secs = $current_time - $past_time;

	  
	  //console.log('The solution is: ', $res.time);
	  console.log("PAST: " + $past_time);
	  console.log("CURR: " + $current_time);
	  console.log("DIFF: " + $diff_in_secs);
	  $mins = 10;
	  if($diff_in_secs < (60 * $mins)) return;//dont send an email
	  else{
	  	 //send an email notification for new intruder
	  	var d = {
	  		"receivers": ["deograciousngereza@gmail.com"],
			"subject": $res.ip + " Activity",
			"body" : MAIL_TPL.getTemplateWithData($res),
			"is_html" : 1
	  	};
	  	//MAIL_TPL.getTemplateWithData($res);
	  	notifyViaEmail(d);
	  } 
	});
	 
	connection.end();
};



/*save to the database*/
var save2DB = function($log_id,$data){
	console.log("-- save to DB --");
	//console.log($data);
	var connection = mysql.createConnection({
	  host     : $SETTINGS.DB_CONN.host,
	  user     : $SETTINGS.DB_CONN.user,
	  password : $SETTINGS.DB_CONN.password,
	  database : $SETTINGS.DB_CONN.database
	});
	 
	connection.connect();
	var q = "UPDATE visitors_list SET ";
	q += "map_name = '" + $data.name + "',";
	q += "map_disp_name = '" + $data.display_name + "',";
	q += "map_country = '" + $data.address.country + "',";
	q += "map_city = '" + $data.address.city + "',";
	q += "map_country_code = '" + $data.address.country_code + "',";
	q += "map_district = '" + $data.address.city_district + "',";
	q += "map_state = '" + $data.address.state + "',";
	q += "map_post_code = '" + $data.address.postcode + "',";
	q += "map_road = '" + $data.address.road + "',";
	q += "map_category = '" + $data.category + "',";
	q += "map_building = '" + ($data.address.building == undefined ? $data.address.suburb : $data.address.building) + "'";
	q += " WHERE code = '" + $log_id + "';";

	//console.log("->" + q);
	//return;
	connection.query(q, function (error, results, fields) {
	  if (error){
	  		console.log("[ DB ERROR ]" + error);
	  		return;
	  }
	  //console.log('The solution is: ', results);
	  checkIfWeCanNotify($log_id);
	});
	 
	connection.end();
};



/*find location from streeet map*/
var get_geo_location = function($log_id,$lat,$lon){
	var conn = axios.get("https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=en&lat="+$lat+"&lon="+$lon+"");
	  conn.then(function (response) {
	  	console.log("FINE:");
	  	var data = response.data;
	  	//console.log(data);
	    save2DB($log_id,data);
	  })
	  .catch(function (error) {
	    //console.log(error);
	    console.log("--ERROR--");
	  });
};



exports.find = function($log_id,$lat,$lon){
	console.log("[ find ] " + $log_id);
	get_geo_location($log_id,$lat,$lon);
};



//checkIfWeCanNotify("a4bcd5e-f4g5hi-2jkl3m3-nopq234");
