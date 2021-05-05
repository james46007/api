<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Alquiler;
use App\Factura;
use App\Factura_Detalle;

class FacturaController extends Controller
{

    public function registrar(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array

        if(!empty($params) && !empty($params_array)){
            //Limpiar datos de espacios de los parametros
            // $params_array = array_map('trim',$params_array);

            //validar datos
            $validate = \Validator::make($params_array,[
                'customer_id' => 'required',
                'date' => 'required',
                'guarantee_id' => 'required',
                'discount' => 'required',
                'subtotal' => 'required',
                'iva' => 'required',
                'iva' => 'required',
                'total' => 'required',
            ]);

            // if($validate->fails() || count($producto_existe)>0 ){
            //     $data =array(
            //         'status' => 'error',
            //         'code' => 404,
            //         'message' => 'El producto ya esta registrado.',
            //         'errors' => $validate->errors(),
            //         'producto'  =>  $producto_existe
            //     );
            // }else{

                $factura = new Factura();

                $factura->customer_id = $params_array['customer_id'];
                $factura->date = $params_array['date'];
                $factura->guarantee_id = $params_array['guarantee_id'];
                $factura->garantia = $params_array['garantia'];
                $factura->discount = $params_array['discount'];
                $factura->subtotal = $params_array['subtotal'];
                $factura->iva = $params_array['iva'];
                $factura->total = $params_array['total'];
                $factura->estado = 1;

                $factura->save();

                $alquiler = new Alquiler();
                $alquiler->invoice_id = $factura->id;
                $alquiler->devuelto = 0;
                $alquiler->estado = 0;
                $alquiler->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El factura se ha creado correctamente.',
                    'factura' => $factura,
                    'alquiler' => $alquiler
                );
            // }
        }else{
            $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Los datos enviados no son correctos'
                );
        }

        return response()->json($data,$data['code']);
    }

    public function getClienteAlquiler($cedula){

        $consulta = "SELECT 
                        customers.id,
                        customers.name,
                        customers.surname,
                        customers.identity_card
                    FROM
                        invoice
                        INNER JOIN rentals ON (invoice.id = rentals.invoice_id)
                        INNER JOIN customers ON (invoice.customer_id = customers.id)
                    WHERE
                        rentals.devuelto = 0 AND 
                        customers.identity_card = {$cedula}";
        $cliente = DB::select($consulta);

        
        if(count($cliente)>0){
            $sql = "SELECT 
                    rentals.id,
                    rentals.devuelto,
                    articles.name AS articulo,
                    articles.code,
                    invoice_detail.quantity,
                    inventory.exi_val_uni,
                    inventory.entrada,
                    invoice.guarantee_id,
                    invoice.garantia,
                    inventory.article_id,
                    inventory.date,
                    inventory.exi_val_uni
                FROM
                    invoice
                    INNER JOIN customers ON (invoice.customer_id = customers.id)
                    INNER JOIN invoice_detail ON (invoice.id = invoice_detail.invoice_id)
                    INNER JOIN articles ON (invoice_detail.article_id = articles.id)
                    INNER JOIN rentals ON (invoice.id = rentals.invoice_id)
                    INNER JOIN inventory ON (articles.id = inventory.article_id)
                WHERE
                    rentals.devuelto = 0 AND 
                    customers.identity_card = {$cedula} AND 
                    inventory.estado = 1";
            $alquiler_existe = DB::select($sql);

            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El cliente tiene articulos alquilado.',
                'cliente' => $cliente[0],
                'alquiler' => $alquiler_existe,
                'garantia' => $alquiler_existe[0]->garantia
            );
        }else{
            $data =array(
                'status' => 'warning',
                'code' => 200,
                'message' => 'El cliente no tiene nada alquilado.',
                'cliente' => $cliente
            );
        }

        return response()->json($data,$data['code']);            
    }

    public function quitarClienteAlquiler($id){
        $sql = "UPDATE rentals SET devuelto = 1 WHERE id = {$id}";
        DB::select($sql);
        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El cliente ha devuelto todo.'
        );
    
        return response()->json($data,$data['code']);        
    }

    public function venta(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array

        $factura = new Factura();

        $factura->customer_id = $params_array['customer_id'];
        $factura->date = $params_array['date'];
        $factura->guarantee_id = $params_array['guarantee_id'];
        $factura->garantia = $params_array['garantia'];
        $factura->discount = $params_array['discount'];
        $factura->subtotal = $params_array['subtotal'];
        $factura->iva = $params_array['iva'];
        $factura->total = $params_array['total'];
        $factura->estado = 1;

        $factura->save();

        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'La factura se ha creado correctamente.',
            'factura' => $factura
        );
        return response()->json($data,$data['code']);
    }

    public function ventaArticulos(Request $request, $facturaID){

        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array



        $facturaDetalle = new Factura_Detalle();

        $facturaDetalle->invoice_id = $facturaID;
        $facturaDetalle->article_id = $params_array['article_id'];
        $facturaDetalle->quantity = $params_array['entrada'];
        $facturaDetalle->val_uni = $params_array['exi_val_uni'];
        $facturaDetalle->val_tot = $facturaDetalle->quantity * $facturaDetalle->val_uni;

        $facturaDetalle->save();
        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El producto se ha creado correctamente.',
            'producto' => $facturaDetalle
        );
        return response()->json($data,$data['code']);
    }
}
