<?
namespace App\Settings\Php;


class Settings{

	/* db connection details */
	public static function DBS($opt = ''){
		switch ($opt) {
			case 'x':return [];break;
			default:
				return [
		            'driver' => 'mysql',
		            'host' => 'localhost',
		            'database' => 'emjeys',
		            'username' => 'grand','port'=>3306,
		            'password' => 'password',
		            'charset'   => 'utf8',
		            'collation' => 'utf8_unicode_ci',
		            'prefix'    => '',
		        ];
				break;
		}
	}



	//queue settings
	//public static function 


}


?>