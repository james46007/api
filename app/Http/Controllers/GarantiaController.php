<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Garantia;

class GarantiaController extends Controller
{
    
    public function register(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array

        if(!empty($params) && !empty($params_array)){
            //Limpiar datos de espacios de los parametros
            // $params_array = array_map('trim',$params_array);

            //validar datos
            $validate = \Validator::make($params_array,[
                'name' => 'required',
            ]);

            $sql = "SELECT * FROM guarantees WHERE name = "."'".$params_array['name']."'";
            $Garantia_existe = DB::select($sql);

            if($validate->fails() || count($Garantia_existe)>0 ){
                $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El Garantia ya esta registrado.',
                    'errors' => $validate->errors(),
                    'Garantia'  =>  $Garantia_existe
                );
            }else{

                $Garantia = new Garantia();

                $Garantia->name = strtoupper($params_array['name']);
                $Garantia->estado = 1;
                $Garantia->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El Garantia se ha creado correctamente.',
                    'Garantia' => $Garantia,
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
        // $total = Garantia::all()->where('estado',1);
        $sql = "SELECT * FROM guarantees WHERE estado = 1";
        $total = DB::select($sql);
        return response()->json($total,200);
    }

    public function borrarGarantia($id){
        // $sql = 'DELETE FROM guarantees where id = '.$id;
        $sql = 'UPDATE guarantees SET estado=0 where id = '.$id;
        $Garantia_eliminado = DB::select($sql);

        return response()->json($Garantia_eliminado,200);
    }

    public function actualizarGarantia(Request $request){
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

            $sql = "SELECT * FROM guarantees WHERE id <> {$params_array['id']} AND name = "."'".$params_array['name']."'";
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
                        'message' => 'La Garantia ya esta registrado.',
                    );
                    return $data;
                }else{
                    $Garantia_actualizado = Garantia::where('id',$id)->update($params_array);

                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                        'Garantia' => $Garantia_actualizado
                    );
                }

                
            } catch (\Throwable $th) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El Garantia ya esta registrado.',
                );
                return $data;
            }         

        }
        return response()->json($data,$data['code']);
    }

    public function getGarantia($id){
        $sql = 'SELECT id,name,estado FROM guarantees where id = '.$id;
        $Garantia = DB::select($sql);

        $data =array(
            'status' => 'success',
            'garantia'  =>  $Garantia[0]
        );

        return response()->json($data,200);
    }
}
