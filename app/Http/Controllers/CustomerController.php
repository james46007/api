<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;
use App\Factura;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function getClientes(){
        $total = Customer::where('estado',1)->get();
        // $sql = "SELECT * FROM categories WHERE estado = 1";
        // $total = DB::select($sql);
        return response()->json($total,200);
    }

    public function getClienteByID($id){
        $cliente = Customer::where('id',$id)->first();

        // $sql = "SELECT 
        //             customers.id,
        //             customers.name,
        //             customers.surname,
        //             customers.identity_card,
        //             customers.direction,
        //             customers.cellphone,
        //             customers.email,
        //             rentals.devuelto
        //         FROM
        //             invoice
        //             INNER JOIN rentals ON (invoice.id = rentals.invoice_id)
        //             INNER JOIN customers ON (invoice.customer_id = customers.id)
        //         WHERE
        //             customers.id = {$id}
        //         AND
        //             rentals.devuelto = 0";
        // $cliente_alquiler = DB::select($sql);

        // if(count($cliente_alquiler) > 0){
        //     $data =array(
        //         'status' => 'warning',
        //         'code' => 200,
        //         'message' => 'El cliente actual tiene articulos alquilados.',
        //         'cliente' => $cliente
        //     );
        //     return response()->json($data,$data['code']);
        // }

        if($cliente != null){
            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El cliente no tiene nada alquilado.',
                'data' => $cliente
            );
            return response()->json($data);
        }

        $data =array(
            'status' => 'registrar',
            'code' => 200,
            'message' => 'Ingrese los datos del cliente.',
            'data' => $cliente
        );
        

        return response()->json($data);
    }
    
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
                'message' => 'El cliente actual tiene articulos alquilados.',
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
            'message' => 'Ingrese los datos del cliente.',
            'cliente' => $cliente
        );
        

        return response()->json($data,$data['code']);
    }

    public function setCliente(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);//array

        $data = array(
            'code' => 100,
            'message' => 'No tiene acceso.',
            'data' => null,
        );
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
                    'surname' => 'required',
                    'identity_card' => 'required',
                    'direction' => 'required',
                    'cellphone' => 'required',
                    'email' => 'required',
                ]);

                $cliente_existe = Customer::where("identity_card",$params_array['identity_card'])
                                            ->where("estado","1")->first();

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
        }
        return response()->json($data,$data['code']);
    }

    public function updateCliente(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);//array

        $data = array(
            'code' => 100,
            'message' => 'No tiene acceso.',
            'data' => null,
        );
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
                    'surname' => 'required',
                    'identity_card' => 'required',
                    'direction' => 'required',
                    'cellphone' => 'required',
                    'email' => 'required',
                ]);

                $id = $params_array['id'];
                $cliente_existe = Customer::where("identity_card",$params_array['identity_card'])
                                        ->where("id","<>",$id)->first();

                if($validate->fails() || !empty($cliente_existe) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 100,
                        'message' => 'El cliente ya esta registrado.',
                        'errors' => $validate->errors(),
                        'cliente'  =>  $cliente_existe
                    );
                }else{

                    //quitar los datos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['create_at']);
                    unset($params_array['estado']);
                    //actualizar el usuario en la bbd

                    $params_array['name'] = strtoupper($params_array['name']);

                    $cliente_actualizada = Customer::where('id',$id)->update($params_array);

                    $data =array(
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El cliente se actualizo correctamente.',
                        'cliente' => $cliente_actualizada
                    );
                }
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

    public function borrarCliente(Request $request, $id){
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
            $disfraz_tiene_categoria = Factura::where('customer_id',$id)->where('estado',1)->first();
            if(!empty($disfraz_tiene_categoria)){
                $data = array(
                    'code' => 100,
                    'message' => 'El cliente tiene facturas generadas.',
                    'data' => $disfraz_tiene_categoria,
                );
                return response()->json($data);
            }

            $articulo_eliminado = Customer::where('id',$id)->update(['estado' => 0]);
            if($articulo_eliminado>0){
                $data = array(
                    'code' => 200,
                    'message' => 'Se ha eliminado correctamente.',
                    'data' => $articulo_eliminado,
                );
            }else{
                $data = array(
                    'code' => 200,
                    'message' => 'No existe el cliente.',
                    'data' => $articulo_eliminado,
                );
            }

        }

        return response()->json($data);
    }
}
