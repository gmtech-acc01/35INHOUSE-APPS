


/*

NOTE

*/

use case : 

- Rabbit mq listeners and workers
- multi lang php and node
- schedules support
- sharable email center




tasklist /fi "username eq userName"
tasklist /fi "imagename eq imageName"

//samp


************* ( response from module ) ********************
	- response_type [EXCEPtION/SENT/OK/ERROR]
	- error_info
	- response_info


******STATUS*******
-PENDING
-SENT / OK /FINE
-EXCEPTION
-ERROR
-INPUT-ERROR

****************** FLOW ***********************
-> sender push a message
   *sender can be: 
   	+] rabittmq pusher
   	+] curl_post
-> sys_ref is returned back
-> Listener picks up and record new entry and assign <sys_ref> return
-> module return a reponse obj to worker 
-> worker update the log to the DB

