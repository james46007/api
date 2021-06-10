<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;
use App\Disfraz_Articulo;
use App\ArticleQuantity;
use Illuminate\Support\Facades\DB;

class ArticuloController extends Controller
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
                    'code' => 'required',
                    'price' => 'required',
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

                // $sql = "SELECT * FROM articles WHERE estado=1 AND code = {$params_array['code']}";
                // $article_exit = DB::select($sql);
                $article_exit = Article::where('code', $params_array['code'])->where('estado', 1)->first();

                if(!empty($article_exit) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'El codigo ya esta registrada.',
                        'errors' => $validate->errors(),
                        'article'  =>  $article_exit
                    );
                    return response()->json($data);
                }

                $nombre = strtoupper($params_array['name']);
                // $sql2 = "SELECT * FROM articles WHERE estado=1 AND name = '{$nombre}'";
                // $articleName_exit = DB::select($sql2);    
                $article_exit = Article::where('name', $params_array['name'])->where('estado', 1)->first();    
                if(!empty($article_exit) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'El nombre del articulo ya esta registrado.',
                        'errors' => $validate->errors(),
                        'article'  =>  $article_exit
                    );
                    return response()->json($data);
                }
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
                
            }else{
                $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'Los datos enviados no son correctos'
                    );
            }

        }else{
            $data =array(
                'status' => 'error',
                'code' => 100,
                'message' => 'No tiene acceso'
            );
        }

        return response()->json($data);
    }

    public function listar(){
        // $total = Article::all()->where('estado',1);
        // $sql = "SELECT * FROM articles WHERE estado = 1";
        // $total = DB::select($sql);
        $total = Article::where('estado',1)->get();
        return response()->json($total,200);
    }

    public function borrarArticulo(Request $request, $id){
        // $sql = 'DELETE FROM articles where id = '.$id;
        // $sql = 'UPDATE articles SET estado=0 where id = '.$id;
        // $articulo_eliminado = DB::select($sql);
        $data = array(
            'code' => 100,
            'message' => 'No tiene acceso.',
            'data' => null,
        );
        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){            
            $disfraz_tiene_categoria = Disfraz_Articulo::where('article_id',$id)->where('estado',1)->first();
            if(!empty($disfraz_tiene_categoria)){
                $data = array(
                    'code' => 100,
                    'message' => 'La articulo esta asociada algunos disfraces.',
                    'data' => $disfraz_tiene_categoria,
                );
                return response()->json($data);
            }

            $alquiler = ArticleQuantity::where('article_id',$id)->where('alquiler','>',0)->first();
            $devuelto = ArticleQuantity::where('article_id',$id)->where('devuelto','>',0)->first();
            $totalDisponible = ArticleQuantity::where('article_id',$id)->where('totalDisponible','>',0)->first();
            
            
            
            if(!empty($alquiler) || !empty($devuelto) || !empty($totalDisponible)){
                $data = array(
                    'code' => 100,
                    'message' => 'No puede borrar tiene articulos en el inventario.',
                    'data' => $alquiler,
                );
                return response()->json($data);
            }

            $articulo_eliminado = Article::where('id',$id)->update(['estado' => 0]);
            ArticleQuantity::where('article_id',$id)->delete();
            $data = array(
                'code' => 200,
                'message' => 'Se ha eliminado correctamente.',
                'data' => $articulo_eliminado,
            );

        }

        return response()->json($data);
    }

    public function actualizarArticulo(Request $request){
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
                    'code' => 'required',
                    'price' => 'required',
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

                $id = $params_array['id'];

                // $sql = "SELECT * FROM articles WHERE id <> {$params_array['id']} AND code = '{$params_array['code']}' AND estado = 1 ";
                // $article_exit = DB::select($sql);
                
                // $sql2 = "SELECT * FROM articles WHERE id <> {$params_array['id']} AND estado=1 AND name = '{$params_array['name']}'";
                // $articleName_exit = DB::select($sql2);
                $article_exit = Article::where('id','<>',$id)->where('code', $params_array['code'])->where('estado', 1)->first();

                if(!empty($article_exit) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'El codigo ya esta asignado al articulo '.$article_exit->name,
                        'errors' => $validate->errors(),
                        'article'  =>  $article_exit
                    );
                    return response()->json($data);
                }

                $nombre = strtoupper($params_array['name']);
                // $sql2 = "SELECT * FROM articles WHERE estado=1 AND name = '{$nombre}'";
                // $articleName_exit = DB::select($sql2);    
                $article_exit = Article::where('id','<>',$id)->where('name', $params_array['name'])->where('estado', 1)->first();    
                if(!empty($article_exit) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'El nombre del articulo ya esta registrado.',
                        'errors' => $validate->errors(),
                        'article'  =>  $article_exit
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

                    $categoria_actualizada = Article::where('id',$id)->update($params_array);

                    $data = array(
                        'code' => 200,
                        'message' => 'Se actualizo correctamente.',
                        'status' => 'success',
                    );
                    
                } catch (\Throwable $th) {
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'El articulo ya esta registrado.',
                    );
                    return $data;
                }         

            }
        }
        return response()->json($data);
    }

    public function getArticulo($id){
        $articulo = Article::where('id',$id)->first();

        $data =array(
            'code' => 200,
            'message' => 'No encontro un registro.',
            'data'  =>  $articulo
        );

        if(!empty($articulo)){
            $data =array(
                'code' => 200,
                'message' => 'Se encontro un registro.',
                'data'  =>  $articulo
            );
        }

        return response()->json($data);
    }
}
