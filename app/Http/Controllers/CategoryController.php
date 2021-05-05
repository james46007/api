<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Category;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{

    public function register(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array

        if(!empty($params) && !empty($params_array)){
            //Limpiar datos de espacios de los parametros
            $params_array = array_map('trim',$params_array);

            //validar datos
            $validate = \Validator::make($params_array,[
                'name' => 'required',
            ]);

            $sql = "SELECT * FROM categories WHERE name = "."'".$params_array['name']."'";
            $categoria_exit = DB::select($sql);

            if($validate->fails() || count($categoria_exit)>0 ){
                $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'La categoria ya esta registrada.',
                    'errors' => $validate->errors(),
                    'categoria'  =>  $categoria_exit
                );
            }else{

                $categoria = new Category();

                $categoria->name = strtoupper($params_array['name']);
                $categoria->estado = 1;
                $categoria->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La categoria se ha creado correctamente.',
                    'categoria' => $categoria,
                    'existe' => $categoria_exit
                );
            }
        }else{
            $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Los datos enviados no son correctos'
                );
        }

        return response()->json($data,$data['code']);
    }

    public function listar(){
        // $total = Category::all()->where('estado',1);
        $sql = "SELECT * FROM categories WHERE estado = 1";
        $total = DB::select($sql);
        return response()->json($total,200);
    }

    public function borrarCategoria($id){
        // $sql = 'DELETE FROM categories where id = '.$id;
        $sql = 'UPDATE categories SET estado=0 where id = '.$id;
        $categoria_eliminado = DB::select($sql);

        return response()->json($categoria_eliminado,200);
    }

    public function actualizarCategoria(Request $request){
        $data = array(
            'code' => 400,
            'status' => 'error',
            'message' => 'Vuelva a intentarlo mas tarde.',
        );

        //recoger los datos del post
        $json = $request->input('json',null);
        $params_array = json_decode($json, true);

        if(!empty($params_array)){

            $id = $params_array['id'];

            $sql = "SELECT * FROM categories WHERE id <> {$params_array['id']} AND name = "."'".$params_array['name']."'";
            $categoria_exit = DB::select($sql);
            try {
                $validate = \Validator::make($params_array,[
                    'name' => 'required',
                ]);

                //quitar los datos que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['create_at']);
                //actualizar el usuario en la bbd

                $params_array['name'] = strtoupper($params_array['name']);

                if( count($categoria_exit) > 0 ){
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'La categoria ya esta registrada.',
                    );
                    return $data;
                }else{
                    $categoria_actualizada = Category::where('id',$id)->update($params_array);

                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                    );
                }

                
            } catch (\Throwable $th) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'La categoria ya esta registrada.',
                );
                return $data;
            }         

        }
        return response()->json($data,$data['code']);
    }

    public function getCategoria($id){
        $sql = 'SELECT id,name,estado FROM categories where id = '.$id;
        $categoria = DB::select($sql);

        $data =array(
            'status' => 'success',
            'categoria'  =>  $categoria
        );

        return response()->json($data,200);
    }
}
