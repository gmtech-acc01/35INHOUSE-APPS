<?php


	

class GHelper{

	/*
		check if date is valid/not valid
	*/
	public function validateDate($date, $format = 'Y-m-d H:i:s') {
	    $d = \DateTime::createFromFormat($format, $date);
	    return $d && $d->format($format) == $date;
	}

	
}

?>