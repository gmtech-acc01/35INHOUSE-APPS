var amqp = require('amqplib/callback_api');
var WORKER = require('./worker.js');



amqp.connect('amqp://localhost', function(err, conn) {
	  conn.createChannel(function(err, ch) {
	    var q = 'gProfileMapFinderQ';

	    ch.assertQueue(q, {durable: true});
	    console.log(" [*] Waiting for messages in %s. To exit press CTRL+C", q);
	    ch.consume(q, function(msg) {
	    	$data = JSON.parse(msg.content.toString());
	      	console.log(" [x] Received %s", msg.content.toString());
	      	WORKER.find($data.code,$data.latitude,$data.longitude);
	    }, {noAck: true});
	  });
});