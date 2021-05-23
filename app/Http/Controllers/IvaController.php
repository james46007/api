<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Iva;
use Illuminate\Support\Facades\DB;

class IvaController extends Controller
{
    public function getIva(){
        $iva = Iva::where('estado',1)->first();       
        // $sql = "SELECT * FROM iva WHERE estado = 1";
        // $total = DB::select($sql);
        $data =array(
            'data' => $iva->iva,
            'code' => 200,
            'message' => 'Se encontro un iva activo.',
        );

        return response()->json($data);
    }

    public function getIvas(){
        $total = Iva::get();       
        // $sql = "SELECT * FROM iva";
        // $total = DB::select($sql);

        return response()->json($total,200);
    }

    public function setIva(Request $request){
        $data =array(
            'status' => 'error',
            'code' => 100,
            'message' => 'No tiene acceso.'
        );
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);//array
        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        if($checkToken){
            //Limpiar datos de espacios de los parametros
            $params_array = array_map('trim',$params_array);

            //validar datos
            if(is_integer($params_array['iva'])){
                $data =array(
                    'status' => 'error',
                    'code' => 100,
                    'message' => 'Campos requeridos.'
                );
                return response()->json($data);
            }
            //valida que no se repita el iva
            $params_array['iva'] = $params_array['iva']/100;
            $categoria_exit = Iva::where('iva', $params_array['iva'])->first();

            if(!empty($categoria_exit) ){
                $data =array(
                    'status' => 'error',
                    'code' => 100,
                    'message' => 'El iva ya esta registrado.',
                    'categoria'  =>  $categoria_exit
                );
                return response()->json($data);
            }
            
            $nuevoIva = new Iva();   
            $nuevoIva->iva = $params_array['iva'];
            $nuevoIva->estado = 0;
            $nuevoIva->save();
            // $sql = "SELECT * FROM iva WHERE estado = 1";
            // $total = DB::select($sql);
            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El iva se ha creado correctamente.',
                'iva' => $nuevoIva,
            );
        }
        return response()->json($data);
    }

    public function setIvas($iva){
        // $iva = Iva::where('estado',1)->first();       
        $sql = "UPDATE iva SET estado = 0";
        $total = DB::select($sql);

        $sql = "UPDATE iva SET estado = 1 WHERE id = {$iva}";
        $total = DB::select($sql);
        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El iva se ha activado correctamente.',
        );
        return response()->json($data);
    }
}
