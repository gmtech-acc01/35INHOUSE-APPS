

case 1: (gprofile app)
********************************
- req is sent from the customer/client
- ip is saved with generated CODE
- r-mq notified ip-recorder (CODE,IP_ADDRESS)
- iprecorder find details geopoints den tell geo finder (CODE,LAT,LON)
- geo finder tell worker to notify via the email for a new intruder

