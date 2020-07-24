<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

;

use App\Category;

class CategoryController extends Controller {

    public function __construct() {
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index() {
        $categories = Category::all();

        return response()->json([
                    'categories' => $categories,
                    'status' => 'success',
                    'code' => 200
        ],200);
    }

    public function show($id) {
        $category = Category::find($id);

        if (is_object($category)) {
            $data = [
                'status' => 'success',
                'code' => 200,
                'category' => $category
            ];
        } else {
            $data = [
                'status' => 'errror',
                'code' => 404,
                'message' => 'La categoría no existe'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request) {
        //Reoger los datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            //Validar datos
            $validate = \Validator::make($params_array, [
                        'name' => 'required'
            ]);

            //Guardar categoría
            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha guardado la categoria'
                ];
            } else {
                $category = new Category();
                $category->name = $params_array['name'];
                $category->save();

                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Categoria guardada correctamente',
                    'category' => $category
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
                        'name' => 'required'
            ]);
            if ($validate->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No se ha actualizado la categoría'
                ];
            } else {
                //Quitar lo que no se quiere actualizar (unset)
                unset($params_array['id']);
                unset($params_array['created_at']);

                //Guardar los cambios
                $category = Category::where('id',$id);;
                
                if ($category->count()>0) {
                    $category->update($params_array);

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Categoría actualizada',
                        'category' => $params_array
                    ];
                } else {
                    $data = [
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'La categoría solicitada no existe'
                    ];
                }
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

}
