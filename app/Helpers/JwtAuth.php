<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'Clave_primer_sistema_inventario_inicio_25_09_2020';
    }

    public function signup($email,$password,$getToken = null){
        $user = User::where([
            'email' => $email,
            'password' => $password,
            'estado' => 1
        ])->first();

        $signup = false;
        if(is_object($user)){
            $signup = true;
        }

        if($signup){

            $sql = 'SELECT rol_id FROM user_roles where estado=1 AND user_id = '.$user->id;
            $roles_usuario = DB::select($sql);

            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'image' => $user->image,
                'description' => $user->description,
                'roles' => $roles_usuario,
                'iat' => time(),
                'exp' => time() + (15*60),
            );

            $jwt = JWT::encode($token,$this->key,'HS256');
            $decode = JWT::decode($jwt,$this->key,['HS256']);

            if(is_null($getToken)){
                $data = $jwt;
            }else{
                $data = $decode;
            }

        }else{
            $data = array(
                'status' => 'error',
                'message' => 'Email o contraseña son incorrectos'
            );
        }

        return $data;
    }

    public function checkToken($jwt,$getIdentity = false){
        $auth = false;
        try{
            $jwt = str_replace('"','',$jwt);
            $decode = JWT::decode($jwt,$this->key,['HS256']);
        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false;
        }

        if(!empty($decode) && is_object($decode) && isset($decode->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity){
            return $decode;
        }

        return $auth;
    }
}
