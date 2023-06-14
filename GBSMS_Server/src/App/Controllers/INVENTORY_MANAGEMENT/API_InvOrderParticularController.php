<?php
namespace App\Controllers\INVENTORY_MANAGEMENT;

use App\Controllers\BaseController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use App\Models\InvOrderParticular;

class API_InvOrderParticularController extends BaseController{


	//tbl fields
	public function tbl_fields($req,$res,$args){
        $TBL = new InvOrderParticular;
        return $res->withJSON($TBL->tbl_fields(),200);
    }

    
    //all
    public function all($req,$res,$args){
        $InvOrderParticular = InvOrderParticular::all();
        $data = [
            "msg_data" => $InvOrderParticular,
            "msg_status" => "OK"
        ];
        return $res->withJSON($data,200);
        //return $res->withJSON($InvOrderParticular,200);
    }



    //find from given order_no
    public function find($req,$res,$args){
        $InvOrderParticular = InvOrderParticular::where("order_no","=",$args['order_no'])->get();
        $data = [
            "msg_data" => $InvOrderParticular,
            "msg_status" => "OK"
        ];
        return $res->withJSON($data,200);
    }



     //get delete
	public function delete($req,$res,$args){
		$InvOrderParticular = InvOrderParticular::where('id','=',$args['id'])->first();
		if(sizeof($InvOrderParticular) == 0){
			$data = [ "msg_data" => "ALREADY DELETED","msg_status" => "FAILED"];
			return $res->withJSON($data,401);
		}
		$InvOrderParticular->delete();
		$data = ["msg_data" => "DATA DELETED","msg_status" => "OK"];
		return $res->withJSON($data,200);
	}

	//insert
	public function insert($req,$res,$args){
		$InvOrderParticular = InvOrderParticular::create($req->getParsedBody());
		$data = [
			"msg_data" => InvOrderParticular::all()->last(),
			"msg_status" => $InvOrderParticular == null ? "FAIL TO INSERT" :"OK"
		];
		//return $res->withJSON($data,200);
		return $res->withJSON(InvOrderParticular::all()->last(),200);
	}

	//update
	public function update($req,$res,$args){
		$updates = $req->getParsedBody();
		$update_status = InvOrderParticular::where('id',$args['id'])
						->update($updates);
		$results = InvOrderParticular::where('id',$args['id'])->first();
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
		$InvOrderParticular = InvOrderParticular::whereRaw("id LIKE '%".$key."%'")->get();
		$data = [
			"msg_data" => $InvOrderParticular,
			"msg_status" => sizeof($InvOrderParticular) == 0 ? "NO RESULTS FOUND" :"OK"
		];
		//return $res->withJSON($data,200);
		return $res->withJSON($InvOrderParticular,200);
	}

}