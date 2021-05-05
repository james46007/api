<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;
use Illuminate\Support\Facades\DB;

class ArticuloController extends Controller
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
                'code' => 'required',
                'price' => 'required',
            ]);

            $sql = "SELECT * FROM articles WHERE estado=1 AND code = {$params_array['code']}";
            $article_exit = DB::select($sql);

            if($validate->fails() || count($article_exit)>0 ){
                $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El codigo ya esta registrada.',
                    'errors' => $validate->errors(),
                    'article'  =>  $article_exit
                );
                return response()->json($data,$data['code']);
            }

            $nombre = strtoupper($params_array['name']);
            $sql2 = "SELECT * FROM articles WHERE estado=1 AND name = '{$nombre}'";
            $articleName_exit = DB::select($sql2);            
            if($validate->fails() || count($articleName_exit)>0 ){
                $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El nombre del articulo ya esta registrado.',
                    'errors' => $validate->errors(),
                    'article'  =>  $article_exit
                );
                return response()->json($data,$data['code']);
            }else{

                $articulo = new Article();

                $articulo->name = strtoupper($params_array['name']);
                $articulo->code = strtoupper($params_array['code']);
                $articulo->price = $params_array['price'];
                $articulo->estado = 1;
                $articulo->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El articulo se ha creado correctamente.',
                    'articulo' => $articulo,
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
        // $total = Article::all()->where('estado',1);
        $sql = "SELECT * FROM articles WHERE estado = 1";
        $total = DB::select($sql);
        return response()->json($total,200);
    }

    public function borrarArticulo($id){
        // $sql = 'DELETE FROM articles where id = '.$id;
        $sql = 'UPDATE articles SET estado=0 where id = '.$id;
        $categoria_eliminado = DB::select($sql);

        return response()->json($categoria_eliminado,200);
    }

    public function actualizarArticulo(Request $request){
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

            $sql = "SELECT * FROM articles WHERE id <> {$params_array['id']} AND code = '{$params_array['code']}' AND estado = 1 ";
            $categoria_exit = DB::select($sql);
            
            $sql2 = "SELECT * FROM articles WHERE id <> {$params_array['id']} AND estado=1 AND name = '{$params_array['name']}'";
            $articleName_exit = DB::select($sql2);  
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
                        'message' => 'El codigo ya esta asignado al articulo '.$categoria_exit[0]->name,
                    );
                    return $data;
                }
                          
                
                if(count($articleName_exit)>0 ){
                    $data =array(
                        'status' => 'error',
                        'code' => 200,
                        'message' => 'El nombre del articulo ya esta registrado.'
                    );
                    return response()->json($data,$data['code']);
                }else{
                    $categoria_actualizada = Article::where('id',$id)->update($params_array);

                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                    );
                }

                
            } catch (\Throwable $th) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El articulo ya esta registrado.',
                );
                return $data;
            }         

        }
        return response()->json($data,$data['code']);
    }

    public function getArticulo($id){
        $sql = 'SELECT id,name,code,price FROM articles where id = '.$id;
        $articulo = DB::select($sql);

        // $articulo = Article::where('id',$id)->first();

        $data =array(
            'status' => 'success',
            'articulo'  =>  $articulo[0]
        );

        return response()->json($data,200);
    }
}
