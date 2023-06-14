<?php
namespace App\Base;

class Listener{
	
    public function __construct() {
    }

   	protected function type_of($var)
	{
	    if (is_array($var)) return "array";
	    if (is_bool($var)) return "boolean";
	    if (is_float($var)) return "float";
	    if (is_int($var)) return "integer";
	    if (is_null($var)) return "NULL";
	    if (is_numeric($var)) return "numeric";
	    if (is_object($var)) return "object";
	    if (is_resource($var)) return "resource";
	    if (is_string($var)) return "string";
	    return "unknown";
	}
	protected function log($message,$log_file = "logs.txt")
	{
	    $myFile = __DIR__.'./../Logs/'.$log_file;
	    $fh = fopen($myFile, 'a') or die("can't open file");
	    @fwrite($fh,"\n". $message); 
	    @fclose($fh);
	}




}





