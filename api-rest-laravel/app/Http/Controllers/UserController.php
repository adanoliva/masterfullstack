<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use App\User;
use App\Helpers\JWTAuth;

class UserController extends Controller {

    public function pruebas(Request $request) {
        return "Acción Pruebas UserController";
    }

    public function register(Request $request) {
        //recoger datos del usuario por post
        $json = $request->input('json', null);
        $params = json_decode($json); //Objeto
        $params_array = json_decode($json, true); //Array
        //limpiar datos
        //validar datos
        if (!empty($params) && !empty($params_array)) {
            $params_array = array_map("trim", $params_array);
            $validate = Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users',
                        'password' => 'required'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => '404',
                    'message' => 'El usuario no se ha creado',
                    'errors' => $validate->errors()
                );
                //return Response::json($data, $data['code']);
            } else {
                //cifrar la contraseña
                //$pwd = password_hash($params->password, PASSWORD_BCRYPT,['cost'=>4]);
                $pwd = hash('sha256', $params->password);
                //comprobar usuario duplicado
                //Poniendo la validación unique:users ya se comprueba directamente si se está duplicando
                //crear el usuario
                $user = new User();
                $user->name = $params_array['name'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->role = 'ROLE_USER';
                $user->password = $pwd;

                //guardar el usuario
                $user->save();

                $data = array(
                    'status' => 'success',
                    'code' => '200',
                    'message' => 'El usuario se ha creado correctamente',
                    'user' => $user
                );
            }
        } else {
            $data = array(
                'status' => 'error',
                'code' => '401',
                'message' => 'Los datos recibidos no son correctos'
            );
        }



        return response()->json($data, $data['code']);
    }

    public function login(Request $request) {

        $jwtAuth = new JWTAuth();

        //recibir datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //Validar datos
        $validate = Validator::make($params_array, [
                    'email' => 'required|email',
                    'password' => 'required'
        ]);
        if ($validate->fails()) {
            $signup = array(
                'status' => 'error',
                'code' => '404',
                'message' => 'Error de validación',
                'errors' => $validate->errors()
            );
            //return Response::json($data, $data['code']);
        } else {
            //cifrar contraseña
            $pwdEncrypt = hash('sha256', $params->password);
            //devolver token o datos
            $gettoken = null;
            if (!empty($params->gettoken)) {
                $gettoken = true;
            }
            $signup = $jwtAuth->singup($params->email, $pwdEncrypt, $gettoken);
        }

        //$pwdEncrypt = password_hash($pwd, PASSWORD_BCRYPT,['cost'=>4]);
        //echo $pwdEncrypt;
        return response()->json($signup, 200);
    }

    public function update(Request $request) {
        $token = $request->header('Authorization');

        $jwtAuth = new JWTAuth();

        $checkToken = $jwtAuth->checktoken($token);

        //REcoger datos por POST
        $json = $request->input('json');
        $params_array = json_decode($json, true);

        //var_dump($checkToken);
        //die();

        if ($checkToken && !empty($params_array)) {

            //Sacar usuario identificado
            $user = $jwtAuth->checktoken($token, true);

            //Validar datos
            $validate = Validator::make($params_array, [
                        'name' => 'required|alpha',
                        'surname' => 'required|alpha',
                        'email' => 'required|email|unique:users,' . $user->sub
            ]);

            //Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //Actualizar usuario BDD
            $user_update = User::where('id', $user->sub)->update($params_array);

            //Devolver array con resultado
            $data = array(
                'status' => 'success',
                'code' => '200',
                'user' => $user,
                'changes' => $params_array
            );
        } else {
            //Mensaje de error
            $data = array(
                'status' => 'error',
                'code' => '400',
                'message' => 'El usuario no está identificado.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request) {

        //Recoger datos imagen
        $image = $request->file('file0');

        //Validación imágen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);



        //Guardar imagen
        if (!$image || $validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => '400',
                'message' => 'Error al subir imagen.'
            );
        } else {
            $image_name = time() . $image->getClientOriginalName();

            \Storage::disk('users')->put($image_name, \File::get($image->getRealPath()));

            $data = array(
                'status' => 'success',
                'code' => '200',
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        $isset = \Storage::disk('users')->exists($filename);
        if ($isset) {
            $file = \Storage::disk('users')->get($filename);

            return new Response($file, 200);
        } else {
            $data = array(
                'status' => 'error',
                'code' => '404',
                'image' => 'La imagen no existe'
            );
            return response()->json($data, $data['code']);
        }
    }

    public function detail($id) {
        $user = User::find($id);

        if (is_object($user)) {
            $data = array(
                'status' => 'success',
                'code' => '200',
                'user' => $user
            );
        } else {
            $data = array(
                'status' => 'error',
                'code' => '404',
                'message' => 'El usuario no exite.'
            );
        }
        return response()->json($data, $data['code']);
    }

}
