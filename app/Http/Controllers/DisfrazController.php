<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Disfraz;
use App\Disfraz_Categoria;
use App\Disfraz_Articulo;
use Illuminate\Support\Facades\DB;

class DisfrazController extends Controller
{

    public function register(Request $request){
        $data =array(
            'status' => 'error',
            'code' => 100,
            'message' => 'No tiene acceso.'
        );
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
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
                    'photo' => 'required',
                    'photoCostume' => 'required',
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

                $params_array['name'] = strtoupper($params_array['name']);
                $disfraz_existe = Disfraz::where('name', $params_array['name'])->where('estado', 1)->first();

                if(!empty($disfraz_existe) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'El disfraz ya esta registrado.',
                        'errors' => $validate->errors(),
                        'categoria'  =>  $disfraz_existe
                    );
                    return response()->json($data);
                }

                $disfraz = new Disfraz();

                $disfraz->name = $params_array['name'];
                $disfraz->photo = $params_array['photo'];
                $disfraz->photoCostume = $params_array['photoCostume'];
                $disfraz->estado = 1;
                $disfraz->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El disfraz se ha creado correctamente.',
                    'categoria' => $disfraz,
                    'existe' => $disfraz_existe
                );
            }else{
                $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'Los datos enviados no son correctos'
                    );
            }
        }

        return response()->json($data);
    }

    public function listar(){
        $total = Disfraz::where('estado',1)->get();
        return response()->json($total);
    }

    public function borrarDisfraz(Request $request, $id){
        // $sql = 'DELETE FROM costumes where id = '.$id;
        $data = array(
            'code' => 100,
            'message' => 'No tiene acceso.',
            'data' => null,
        );
        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            // $sql = 'UPDATE costumes SET estado=0 where id = '.$id;
            // $categoria_eliminado = DB::select($sql);

            $categoria_eliminado = Disfraz::where('id',$id)->update(['estado' => 0]);
            Disfraz_Articulo::where('costume_id',$id)->update(['estado'=>0]);
            Disfraz_Categoria::where('costume_id',$id)->update(['estado'=>0]);
            $data = array(
                'code' => 200,
                'message' => 'Se ha eliminado correctamente.',
                'data' => $categoria_eliminado,
            );
        }
        return response()->json($data);
    }

    public function actualizarDisfraz(Request $request){
        $data = array(
            'code' => 100,
            'status' => 'error',
            'message' => 'No tiene acceso.',
        );

        //recoger los datos del post
        $json = $request->input('json',null);
        $params_array = json_decode($json, true);
        //Limpiar datos de espacios de los parametros
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
                $params_array['name'] = strtoupper($params_array['name']);

                // $sql = "SELECT * FROM costumes WHERE id <> {$params_array['id']} AND name = "."'".$params_array['name']."'";
                // $disfraz_existe = DB::select($sql);
                $disfraz_existe = Disfraz::where('id','<>',$id)->where('estado',1)->where('name',$params_array['name'])->first();
                if( !empty($disfraz_existe) ){
                    $data = array(
                        'code' => 100,
                        'message' => 'El disfraz ya esta registrado.',
                        'data' => $disfraz_existe,
                    );
                    return response()->json($data);
                }
                try {
                    //quitar los datos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['create_at']);
                    unset($params_array['estado']);
                    //actualizar el usuario en la bbd
                    $categoria_actualizada = Disfraz::where('id',$id)->update($params_array);

                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Se guardo correctamente.',
                    );
                    
                } catch (\Throwable $th) {
                    $data = array(
                        'code' => 100,
                        'status' => 'error',
                        'message' => 'Error al guardar.',                        
                    );
                    return $data;
                }         

            }
        }
        return response()->json($data);
    }

    public function getDisfraz($id){
        $disfraz = Disfraz::where('id',$id)->first();

        $data =array(
            'status' => 'success',
            'data'  =>  $disfraz
        );

        return response()->json($data);
    }

    public function actualizarFoto(Request $request){
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
                'photo' => 'required',
            ]);

            $sql = "UPDATE costumes SET photo= '".$params_array['photo']."' WHERE id = ".$params_array['id'];
            $disfraz_existe = DB::select($sql);


            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El disfraz se ha actualizado correctamente.',
                'existe' => $disfraz_existe,
                'sql'=>$sql
            );
            
        }else{
            $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Los datos enviados no son correctos'
                );
        }

        return response()->json($data,$data['code']);
    }

    public function actualizarFotoProbador(Request $request){
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
                'photo' => 'required',
            ]);

            $sql = "UPDATE costumes SET photoCostume= '".$params_array['photoCostume']."' WHERE id = ".$params_array['id'];
            $disfraz_existe = DB::select($sql);


            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El disfraz se ha actualizado correctamente.',
                'existe' => $disfraz_existe,
                'sql'=>$sql
            );
            
        }else{
            $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Los datos enviados no son correctos'
                );
        }

        return response()->json($data,$data['code']);
    }

    public function guardarImagenServidor(Request $request){
        //recoger archivos
        $image = $request->file('file0');

        // $validate = \Validator::make($request->all(), [
        //     'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        // ]);

        if(!$image){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.'
            );
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('disfraces')->put($image_name, \File::get($image));
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        return response($data,$data['code'])->header('Content-Type','text/plain');
    }

    public function getImage($filename){
        $isset = \Storage::disk('disfraces')->exists($filename);

        if($isset){
            $file = \Storage::disk('disfraces')->get($filename);
            return new Response($file,200);
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'La imagen no existe.'
            );
            return response()->json($data, $data['code']);
        }
    }

    public function mostrarCategoriasDisfraz($id){
        $sql = 'SELECT categories.name, categories.id 
                FROM categories INNER JOIN costumes_category 
                ON (categories.id = costumes_category.category_id) 
                INNER JOIN costumes ON (costumes_category.costume_id = costumes.id) 
                WHERE costumes_category.estado=1 AND categories.estado = 1 AND costumes.id = '.$id;
        $categorias_disfraz = DB::select($sql);

        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'La categoria se agregado al disfraz correctamente.',
            'categorias' => $categorias_disfraz,
        );
        return response()->json($data,$data['code']);
    }
    
    public function agregarCategoria($disfrazId,$categoriaId){

        $sql = "SELECT * FROM costumes_category WHERE costume_id = ".$disfrazId." and category_id = ".$categoriaId." and estado = 1";
        $disfraz_existe = DB::select($sql);
        
        if(count($disfraz_existe)>0 ){
            $data =array(
                'status' => 'error',
                'code' => 100,
                'message' => 'El disfraz ya esta registrado con la categoria.',
                'categoria'  =>  $disfraz_existe
            );
        }else{
            
            $disfrazCategoria = new Disfraz_Categoria();     
            
            $disfrazCategoria->costume_id = $disfrazId;
            $disfrazCategoria->category_id = $categoriaId;
            $disfrazCategoria->estado = 1;
            
            $disfrazCategoria->save();
            
            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'La categoria se agregado al disfraz correctamente.',
                'categorias' => $disfrazCategoria,
            );
        }
        
        return response()->json($data);
    }
    
    public function quitarCategoria($disfrazId,$categoriaId){
        // $sql = 'DELETE FROM costumes_category where costume_id = '.$disfrazId.' and category_id='.$categoriaId;
        $sql = 'UPDATE costumes_category SET estado=0 where costume_id = '.$disfrazId.' and category_id='.$categoriaId;
        $categoria_eliminado = DB::select($sql);

        $data =array(
            'status' => 'error',
            'code' => 200,
            'message' => 'Se elimino la categoria del disfraz.'
        );
        return response()->json($data);
    }
    
    public function mostrarArticulosDisfraz($id){
        $sql = 'SELECT articles.name, articles.id 
                FROM articles INNER JOIN articles_costume 
                ON (articles.id = articles_costume.article_id) 
                INNER JOIN costumes ON (articles_costume.costume_id = costumes.id) 
                WHERE articles_costume.estado=1 AND articles.estado = 1 AND costumes.id = '.$id;
        $categorias_disfraz = DB::select($sql);
    
        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El articulo se agregado al disfraz correctamente.',
            'categorias' => $categorias_disfraz,
        );
        return response()->json($data,$data['code']);
    }

    public function agregarArticulo($disfrazId,$articleId){

        $sql = "SELECT * FROM articles_costume WHERE costume_id = ".$disfrazId." and article_id = ".$articleId." and estado = 1";
        $disfraz_existe = DB::select($sql);
        
        if(count($disfraz_existe)>0 ){
            $data =array(
                'status' => 'error',
                'code' => 100,
                'message' => 'El disfraz ya esta registrado con el articulo.',
                'categoria'  =>  $disfraz_existe
            );
        }else{
            
            $disfrazArticulo = new Disfraz_Articulo();     
            
            $disfrazArticulo->costume_id = $disfrazId;
            $disfrazArticulo->article_id = $articleId;
            $disfrazArticulo->estado = 1;
            
            $disfrazArticulo->save();
            
            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'La articulo se agregado al disfraz correctamente.',
                'categorias' => $disfrazArticulo,
            );
        }
        
        return response()->json($data);
    }

    public function quitarArticulo($disfrazId,$categoriaId){
        // $sql = 'DELETE FROM articles_costume where costume_id = '.$disfrazId.' and article_id='.$categoriaId;
        $sql = 'UPDATE articles_costume SET estado = 0 where costume_id = '.$disfrazId.' and article_id='.$categoriaId;
        $categoria_eliminado = DB::select($sql);
        
        $data =array(
            'status' => 'error',
            'code' => 200,
            'message' => 'Se elimino el articulo del disfraz.'
        );
        return response()->json($data);
    }

    public function categoriaDisfraces($id){
        $sql = "SELECT 
        costumes.name,
        costumes.photo,
        costumes.photoCostume,
        costumes.id,
        categories.name as nombre
      FROM
        categories
        INNER JOIN costumes_category ON (categories.id = costumes_category.category_id)
        INNER JOIN costumes ON (costumes_category.costume_id = costumes.id)
      WHERE
        costumes_category.estado = 1
      AND
        costumes.estado = 1
      AND
        categories.id = {$id}";
        $disfraces_categoria = DB::select($sql);

        $categoria = DB::select("SELECT 
        categories.name,
        categories.id
      FROM
        categories
      WHERE
        categories.estado = 1 
      AND
        categories.id = {$id}");

        $data = array(
            "disfraces" => $disfraces_categoria,
            "categoria" => $categoria
        );
        
        return response()->json($data,200);
    }
}
