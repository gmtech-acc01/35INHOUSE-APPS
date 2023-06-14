<?php
namespace App\Controllers\COURIER_API;

use App\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\Wh_Product;

class API_Wh_ProductController extends BaseController{

    //all
    public function all($req,$res,$args){
        $Wh_Product = Wh_Product::all();
        $data = [
            "msg_data" => $Wh_Product,
            "msg_status" => "OK"
        ];
        //return $res->withJSON($data,200);
        return $res->withJSON($Wh_Product,200);
    }

    //find from given id
    public function find($req,$res,$args){
        $Wh_Product = Wh_Product::where("id","=",$args['id'])->first();
        $data = [
            "msg_data" => $Wh_Product,
            "msg_status" => "OK"
        ];
        //return $res->withJSON($data,200);
		return $res->withJSON($Wh_Product,200);
    }

     //get delete
	public function delete($req,$res,$args){
		$Wh_Product = Wh_Product::where('id','=',$args['id'])->first();
		if(sizeof($Wh_Product) == 0){
			$data = [ "msg_data" => "ALREADY DELETED","msg_status" => "FAILED"];
			return $res->withJSON($data,401);
		}
		$Wh_Product->delete();
		$data = ["msg_data" => "DATA DELETED","msg_status" => "OK"];
		return $res->withJSON($data,200);
	}

	//insert
	public function insert($req,$res,$args){
		$Wh_Product = Wh_Product::create($req->getParsedBody());
		$data = [
			"msg_data" => Wh_Product::all()->last(),
			"msg_status" => $Wh_Product == null ? "FAIL TO INSERT" :"OK"
		];
		//return $res->withJSON($data,200);
		return $res->withJSON(Wh_Product::all()->last(),200);
	}

	//update
	public function update($req,$res,$args){
		$updates = $req->getParsedBody();
		$update_status = Wh_Product::where('id',$args['id'])
						->update($updates);
		$results = Wh_Product::where('id',$args['id'])->first();
		$data = [
			"msg_data" => $results,
			"msg_status" => $update_status == 1 ? "FAIL TO UPDATE" :"OK"
		];
		//return $res->withJSON($data,200);
		return $res->withJSON($results,200);
	}

	//search
	public function search($req,$res,$args){
		$key = trim($req->getQueryParams()['key'],"'");
		$Wh_Product = Wh_Product::whereRaw("id LIKE '%".$key."%'")->get();
		$data = [
			"msg_data" => $Wh_Product,
			"msg_status" => sizeof($Wh_Product) == 0 ? "NO RESULTS FOUND" :"OK"
		];
		//return $res->withJSON($data,200);
		return $res->withJSON($Wh_Product,200);
	}

}