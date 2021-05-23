<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use App\Disfraz_Categoria;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{

    public function register(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array

        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){

            if(!empty($params_array)){
                //Limpiar datos de espacios de los parametros
                $params_array = array_map('trim',$params_array);

                //validar datos
                $validate = \Validator::make($params_array,[
                    'name' => 'required',
                ]);

                if($validate->fails()){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'Campos requeridos.',
                        'errors' => $validate->errors()
                    );
                    return response()->json($data);
                }

                $categoria_exit = Category::where('name', $params_array['name'])->where('estado', 1)->first();

                if(!empty($categoria_exit) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'La categoria ya esta registrada.',
                        'errors' => $validate->errors(),
                        'categoria'  =>  $categoria_exit
                    );
                    return response()->json($data);
                }
                $categoria = new Category();

                $categoria->name = strtoupper($params_array['name']);
                $categoria->estado = 1;
                $categoria->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La categoria se ha creado correctamente.',
                    'categoria' => $categoria
                );
            }else{
                $data =array(
                        'status' => 'error',
                        'code' => 400,
                        'message' => 'Los datos enviados no son correctos'
                    );
            }

        }else{
            $data =array(
                'status' => 'error',
                'code' => 100,
                'message' => 'No tiene acceso.'
            );
        }

        return response()->json($data);
    }

    public function listar(){
        $total = Category::where('estado',1)->get();
        // $sql = "SELECT * FROM categories WHERE estado = 1";
        // $total = DB::select($sql);
        return response()->json($total,200);
    }

    public function borrarCategoria(Request $request, $id){
        // $sql = 'DELETE FROM categories where id = '.$id;
        // $sql = 'UPDATE categories SET estado=0 where id = '.$id;
        // $categoria_eliminado = DB::select($sql);
        $data = array(
            'code' => 100,
            'message' => 'No tiene acceso.',
            'data' => null,
        );
        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            $disfraz_tiene_categoria = Disfraz_Categoria::where('category_id',$id)->where('estado',1)->first();
            if(!empty($disfraz_tiene_categoria)){
                $data = array(
                    'code' => 100,
                    'message' => 'La categoria esta asociada algunos disfraces.',
                    'data' => $disfraz_tiene_categoria,
                );
                return response()->json($data);
            }

            $categoria_eliminado = Category::where('id',$id)->update(['estado' => 0]);
            $data = array(
                'code' => 200,
                'message' => 'Se ha eliminado correctamente.',
                'data' => $categoria_eliminado,
            );
        }

        return response()->json($data);
    }

    public function actualizarCategoria(Request $request){
        $data = array(
            'code' => 100,
            'message' => 'No tiene acceso.',
            'data' => null,
        );

        //recoger los datos del post
        $json = $request->input('json',null);
        $params_array = json_decode($json, true);
        //Limpia los espacios en blanco
        $params_array = array_map('trim',$params_array);

        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            if(!empty($params_array)){
                $validate = \Validator::make($params_array,[
                    'name' => 'required',
                ]);
                if($validate->fails()){
                    $data = array(
                        'code' => 100,
                        'message' => 'Campos vacios.',
                        'data' => null,
                    );
                    return response()->json($data);
                }

                $id = $params_array['id'];

                // $sql = "SELECT * FROM categories WHERE id <> {$params_array['id']} AND estado=1 AND name = "."'".$params_array['name']."'";
                // $categoria_exit = DB::select($sql);
                $categoria_exit = Category::where('id','<>',$id)->where('estado',1)->where('name',$params_array['name'])->first();
                if( !empty($categoria_exit) ){
                    $data = array(
                        'code' => 100,
                        'message' => 'La categoria ya esta registrada.',
                        'data' => $categoria_exit,
                    );
                    return response()->json($data);
                }
                try {
                    //quitar los datos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['create_at']);
                    unset($params_array['estado']);
                    //actualizar el usuario en la bbd
                    $params_array['name'] = strtoupper($params_array['name']);                
                    $categoria_actualizada = Category::where('id',$id)->update($params_array);
                    $data = array(
                        'code' => 200,
                        'message' => 'Se actualizo correctamente',
                        'data' => $categoria_actualizada
                    );
                    return response()->json($data);

                } catch (\Throwable $th) {
                    $data = array(
                        'code' => 400,
                        'message' => 'Error al actualizar.',
                        'data' => null,
                    );
                    return response()->json($data);
                }         

            }
        }
        return response()->json($data);
    }

    public function getCategoria($id){
        $categoria = Category::where('id', $id)->first();

        $data =array(
            'code' => 200,
            'message' => 'No encontro un registro.',
            'data'  =>  $categoria
        );

        if(!empty($categoria)){
            $data =array(
                'code' => 200,
                'message' => 'Se encontro un registro.',
                'data'  =>  $categoria
            );
        }

        return response()->json($data);
    }
}
