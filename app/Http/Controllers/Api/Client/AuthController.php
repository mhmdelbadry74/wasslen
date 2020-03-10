<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPassword;
use DB;


use Twilio\Rest\Client;
use App\Models\DriClient;

class AuthController extends Controller {
    public function register(Request $request){
        $valditor=Validator()->make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:driclients',
            'phone' => 'required|unique:driclients',
            'nid' => 'required',
            'gender' => 'required|in:male,fmale' ,
            'type' => 'required|in:client,driver' ,
           


        ]);
        if ($valditor->fails()) {

            return responsejson(0,"failed",$valditor->errors());
        }
        if ($request->type == 'client') {
            $clients=DriClient::create($request->all());
            $clients->api_token=str_random(60);
            $clients->statue = $clients->statue ?? 'active' ;
            $clients->save();
            return responsejson(1,'تم الاضافة بنجاخ',[
                'api_token' => $clients->api_token,
                'clients' => $clients ,
            ]); 
        }else{
            $clients=DriClient::create($request->all());
            $clients->api_token=str_random(60);
            $clients->statue = $clients->statue ?? 'desactive' ;
            $clients->save();
            return responsejson(1,'تم الاضافة بنجاخ',[
                'api_token' => $clients->api_token,
                'clients' => $clients ,
            ]); 
        }
       
    }

    public function login(Request $request){
        $validator = validator()->make($request->all(), [
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return responsejson(0, $validator->errors()->first(), $validator->errors());
        }
        $client = DriClient::where('phone' , $request->phone)->get()->toArray();

     
if ($client) {
    $user_id = $client[0]['id'];
    $user_type = $client[0]['type'];

    if ($user_type == 'client') {
        $data= DB::table('driclients')
        ->join('client_profiles','driclients.id','=','client_profiles.driclient_id')
        ->select('*','driclients.id As userid','client_profiles.id As ProfileID')->where('driclients.id',$user_id)->get()->toArray();
        if (empty($data)) {
            $data= DB::table('driclients')->select('*','id As userid')->where('id',$user_id)->get()->toArray();
        }

        return responsejson(1,' مرحبا بك ' , $data); 
    }else {
        $data= DB::table('driclients')
        ->join('driver_profiles','driclients.id','=','driver_profiles.driclient_id')
        ->join('cars','driver_profiles.driclient_id','=','cars.driclient_id')
        ->where('driclients.id',$user_id)->get()->toArray();

        if (empty($data)) {
            $data= DB::table('driclients')
            ->join('driver_profiles','driclients.id','=','driver_profiles.driclient_id')
            ->where('driclients.id',$user_id)->get()->toArray();

            if (empty($data)) {
                $data= DB::table('driclients')->where('id',$user_id)->get()->toArray();
            }
        }
        return responsejson(1,' مرحبا بك  ' , $data); 
    }



        }else{
            return responseJson(0,'لا يوجد حساب');
        }
    
    }




    public function code_mobile(Request $request){
        $validator = validator()->make($request->all(), [
            'pin_code_phone' => 'required',
        ]);
        if ($validator->fails()) {
            return responsejson(0, $validator->errors()->first(), $validator->errors());
        }
        $client = DriClient::where('pin_code_phone' , $request->pin_code_phone)->first();
        if ($client) {
            return responsejson(1,'تم تسجيل الدخول بنجاح ',[
                'client' => $client ,
            ]);
        }else{
            return responseJson(0,'الرمز غير صالح');
        }
       
    }


    public function check_token(Request $request){
        $validator = validator()->make($request->all(), [
            'phone' => 'required',
        ]);
        if ($validator->fails()) {
            return responsejson(0, $validator->errors()->first(), $validator->errors());
        }
        $client = DriClient::select('api_token','type','id')->where('phone' , $request->phone)->first();
     //   dd($request->api_token);
        if ($client) {
            $client->phone;
            $client->save();
  return responseJson(1, 'مرحبا بك ', [
                
                'client' => $client
            ]);
        }else{
            return responseJson(0,'لا يوجد حساب');
        }
    
    }
}
?>
