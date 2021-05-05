<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function getCliente($cedula){
        $cliente = Customer::where('identity_card',$cedula)->first();

        $sql = "SELECT 
                    customers.id,
                    customers.name,
                    customers.surname,
                    customers.identity_card,
                    customers.direction,
                    customers.cellphone,
                    customers.email,
                    rentals.devuelto
                FROM
                    invoice
                    INNER JOIN rentals ON (invoice.id = rentals.invoice_id)
                    INNER JOIN customers ON (invoice.customer_id = customers.id)
                WHERE
                    customers.identity_card = {$cedula}
                AND
                    rentals.devuelto = 0";
        $cliente_alquiler = DB::select($sql);

        if(count($cliente_alquiler) > 0){
            $data =array(
                'status' => 'warning',
                'code' => 200,
                'message' => 'El cliente actual tiene alquilado articulos.',
                'cliente' => $cliente
            );
            return response()->json($data,$data['code']);
        }

        if($cliente != null){
            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El cliente no tiene nada alquilado.',
                'cliente' => $cliente
            );
            return response()->json($data,$data['code']);
        }

        $data =array(
            'status' => 'registrar',
            'code' => 200,
            'message' => 'El cliente no esta registrado.',
            'cliente' => $cliente
        );
        

        return response()->json($data,$data['code']);
    }

    public function setCliente(Request $request){
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
                'surname' => 'required',
                'identity_card' => 'required',
                'direction' => 'required',
                'cellphone' => 'required',
                'email' => 'required',
            ]);

            $cliente_existe = Customer::where("identity_card",$params_array['identity_card'])->first();

            if($validate->fails() || !empty($cliente_existe) ){
                $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El cliente ya esta registrado.',
                    'errors' => $validate->errors(),
                    'cliente'  =>  $cliente_existe
                );
            }else{

                $cliente = new Customer();

                $cliente->name = strtoupper($params_array['name']);
                $cliente->surname = strtoupper($params_array['surname']);
                $cliente->identity_card = $params_array['identity_card'];
                $cliente->direction = strtoupper($params_array['direction']);
                $cliente->cellphone = $params_array['cellphone'];
                $cliente->email = $params_array['email'];
                $cliente->estado = 1;

                $cliente->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El cliente se ha creado correctamente.',
                    'cliente' => $cliente
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
}
