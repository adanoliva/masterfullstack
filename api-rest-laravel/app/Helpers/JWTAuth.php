<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;

class JWTAuth {

    public $key;

    public function __construct() {
        $this->key = "_clave_secreta_225588";
    }

    public function singup($email, $password, $gettoken = null) {
        //Buscar si existe el usuario con las credenciales (email+pass)
        $user = User::where([
                    'email' => $email,
                    'password' => $password
                ])->first();



        //Comprobar si son correctas
               $signup = false;
        if(is_object($user)){
            $signup = true;
        }


        //Generar el TOKEN
        if ($signup) {
            $token = array(
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'description' => $user->description,
                'image' => $user->image,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            //Devolver los datos decodificados o el TOKEN en función de un parámetro
            if (is_null($gettoken)) {
                $data = $jwt;
            } else {
                $data = $decoded;
            }
        } else {
            $data = array([
                    'status' => 'error',
                    'message' => 'login incorrecto'
            ]);
        }

        return $data;
    }

    public function checktoken($token, $getIdentity = false) {
        $auth = false;

        try {
            $token = str_replace('"', '', $token);
            $decoded = JWT::decode($token, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e) {
            $auth = false;
        }
        
        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub) )
        {
            $auth = true;
        }else{
            $auth = false;
        }
        
        
        if ($getIdentity)
        {
            return $decoded;
        }
        return $auth;
    }

}
