<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Garantia;
use App\Factura;

class GarantiaController extends Controller
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
        //Limpiar datos de espacios de los parametros
        $params_array = array_map('trim',$params_array);

        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            if(!empty($params_array)){
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

                // $sql = "SELECT * FROM guarantees WHERE name = "."'".$params_array['name']."'";
                // $Garantia_existe = DB::select($sql);
                $Garantia_existe = Garantia::where('name', $params_array['name'])->where('estado', 1)->first();

                if(!empty($Garantia_existe) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'El Garantia ya esta registrado.',
                        'errors' => $validate->errors(),
                        'Garantia'  =>  $Garantia_existe
                    );
                    return response()->json($data);
                }
                $Garantia = new Garantia();

                $Garantia->name = strtoupper($params_array['name']);
                $Garantia->estado = 1;
                $Garantia->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El Garantia se ha creado correctamente.',
                    'data' => $Garantia,
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
        // $sql = "SELECT * FROM guarantees WHERE estado = 1";
        // $total = DB::select($sql);
        $total = Garantia::where('estado',1)->get();
        return response()->json($total,200);
    }

    public function borrarGarantia(Request $request, $id){
        // $sql = 'DELETE FROM guarantees where id = '.$id;
        // $sql = 'UPDATE guarantees SET estado=0 where id = '.$id;
        // $garantia_eliminado = DB::select($sql);
        $data = array(
            'code' => 100,
            'message' => 'No tiene acceso.',
            'data' => null,
        );
        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            $garantia_tiene_facturas = Factura::where('guarantee_id',$id)->where('estado',1)->first();
            if(!empty($garantia_tiene_facturas)){
                $data = array(
                    'code' => 100,
                    'message' => 'La garantia esta asociada algunas facturas.',
                    'data' => $garantia_tiene_facturas,
                );
                return response()->json($data);
            }

            $garantia_eliminado = Garantia::where('id',$id)->update(['estado' => 0]);
            $data = array(
                'code' => 200,
                'message' => 'Se ha eliminado correctamente.',
                'data' => $garantia_eliminado,
            );
        }
        return response()->json($data);
    }

    public function actualizarGarantia(Request $request){
        $data = array(
            'code' => 100,
            'message' => 'No tiene acceso.',
            'data' => null,
        );
        //recoger los datos del post
        $json = $request->input('json',null);
        $params_array = json_decode($json, true);
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

                // $sql = "SELECT * FROM guarantees WHERE id <> {$params_array['id']} AND name = "."'".$params_array['name']."'";
                // $garantia_exite = DB::select($sql);
                
                $garantia_exite = Garantia::where('id','<>',$id)->where('estado',1)->where('name',$params_array['name'])->first();
                if( !empty($garantia_exite) ){
                    $data = array(
                        'code' => 100,
                        'message' => 'La garantia ya esta registrada.',
                        'data' => $garantia_exite,
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
                    
                    $Garantia_actualizado = Garantia::where('id',$id)->update($params_array);

                    $data = array(
                        'code' => 200,
                        'message' => 'Se actualizo correctamente.',
                        'Garantia' => $Garantia_actualizado
                    );          
                    response()->json($data);  

                } catch (\Throwable $th) {
                    $data = array(
                        'code' => 100,
                        'status' => 'error',
                        'message' => 'Error al actualizar.'
                    );
                    response()->json($data);
                }         

            }
        }
        return response()->json($data);
    }

    public function getGarantia($id){
        // $sql = 'SELECT id,name,estado FROM guarantees where id = '.$id;
        // $Garantia = DB::select($sql);

        // $data =array(
        //     'status' => 'success',
        //     'garantia'  =>  $Garantia[0]
        // );

        $garantia = Garantia::where('id', $id)->first();

        $data =array(
            'code' => 200,
            'message' => 'No encontro un registro.',
            'data'  =>  $garantia
        );

        if(!empty($garantia)){
            $data =array(
                'code' => 200,
                'message' => 'Se encontro un registro.',
                'data'  =>  $garantia
            );
        }
        return response()->json($data,200);
    }
}
