<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Helpers\JWTAuth;
use App\Post;

class PostController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => 
            ['index', 
            'show', 
            'getImage',
            'getPostsbyCategory',
            'getPostsbyUser']
            ]);
    }

    public function index() {
        $posts = Post::all()->load('category');

        return response()->json([
                    'posts' => $posts,
                    'status' => 'success',
                    'code' => 200
                        ], 200);
    }

    public function show($id) {
        $post = Post::find($id);

        if (is_object($post)) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'post' => $post
            ];
        } else {
            $data = [
                'status' => 'errror',
                'code' => 404,
                'message' => 'La entrada no existe.'
            ];
        }
        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //Conseguir usuario identificado
            $user = $this->getIdentity($request);

            //Validar datos
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required',
                        'image' => 'required'
            ]);

            //Guardar categoría
            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'No se ha guardado la entrada porque faltan datos.'
                ];
            } else {
                $post = new Post();
                $post->user_id = $user->sub;
                $post->title = $params_array['title'];
                $post->content = $params_array['content'];
                $post->category_id = $params_array['category_id'];
                $post->image = $params_array['image'];
                $post->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Entrada guardada correctamente',
                    'post' => $post
                ];
            }
        } else {

            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'No se han recibido datos'
            ];
        }
        //Devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request) {
        //Recoger los datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);


        if (!empty($params_array)) {
            //Validar los datos
            $validate = \Validator::make($params_array, [
                        'title' => 'required',
                        'content' => 'required',
                        'category_id' => 'required'
            ]);
            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha actualizado la entrada'
                ];
            } else {
                //Quitar lo que no se quiere actualizar (unset)
                unset($params_array['id']);
                unset($params_array['created_at']);
                unset($params_array['image']);

                //Conseguir usuario identificado
                $user = $this->getIdentity($request);

                //consegiir el post 
                $post = Post::where('id', $id)
                                ->where('user_id', $user->sub)->first();

                if (!empty($post) && is_object($post)) {
                    $post->update($params_array);

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Entrada actualizada',
                        'post' => $post,
                        'changes' => $params_array
                    ];
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Datos de envío incorrectos'
                    ];
                }

                //Guardar los cambios
                /* $where = [
                  'id' => $id,
                  'user_id' => $user->sub
                  ];

                  $params_array['user_id'] = $user->sub;


                  $post = Post::updateOrCreate($where, $params_array);
                 */
            }
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'No se han recibido datos'
            ];
        }

        //Devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function destroy($id, Request $request) {

        //Conseguir usuario identificado
        $user = $this->getIdentity($request);

        //consegiir el post 
        $post = Post::where('id', $id)
                        ->where('user_id', $user->sub)->first();

        //comprobar si no está vacío
        if (!empty($post)) {
            //borrar registro
            $post->delete();

            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'Entrada actualizada',
                'post' => $post
            ];
        } else {
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'El post indicado no existe'
            ];
        }


        //Devolver el resultado
        return response()->json($data, $data['code']);
    }

    private function getIdentity($request) {
        $jwtAuth = new JWTAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checktoken($token, true);
        return $user;
    }

    public function upload(Request $request) {
        //recoger la imagen de la peticion
        $image = $request->file('file0');

        //validar la imagen
        $validate = \Validator::make($request->all(), [
                    'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //guardar la imagen en disco
        if (!$image || $validate->fails()) {
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'Error al validar la imagen.'
            ];
        } else {
            $image_name = time() . $image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));
            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'Imagen subida correctamente.',
                'image' => $image_name
            ];
        }

        //devolver datos
        return response()->json($data, $data['code']);
    }

    public function getImage($filename) {
        //comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);
        if ($isset) {
            //conseguir la imagen
            $file = \Storage::disk('images')->get($filename);
            //devolver imagen
            return new Response($file, 200);
        } else {
            //devolver error
            $data = [
                'status' => 'error',
                'code' => 400,
                'message' => 'La imagen no existe.'
            ];
        }
        //devolver datos
        return response()->json($data, $data['code']);
    }

    public function getPostsbyCategory($id) {
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'posts' => $posts
                        ], 200);
    }

    public function getPostsbyUser($id) {
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
                    'status' => 'success',
                    'posts' => $posts
                        ], 200);
    }

}
