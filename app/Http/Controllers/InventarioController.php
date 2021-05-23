<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Inventario;
use App\Factura_Detalle;

class InventarioController extends Controller
{

    public function register(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params_array = json_decode($json,true);//array

        if(!empty($params_array)){
            //Limpiar datos de espacios de los parametros
            // $params_array = array_map('trim',$params_array);

            //validar datos
            $validate = \Validator::make($params_array,[
                'article_id' => 'required',
                'date' => 'required',
                'description' => 'required',
                'entrada' => 'required',
                'ent_val_uni' => 'required',
                'estado' => 'required',
            ]);

            $sql = "SELECT * FROM inventory WHERE article_id = {$params_array['article_id']} and estado =1 ORDER by ID DESC";
            $producto_existe = DB::select($sql);

            if(count($producto_existe) > 0){
                $sql2 = "UPDATE inventory SET estado = 0 WHERE id = {$producto_existe[0]->id}";
                DB::select($sql2);
            }

            // if($validate->fails() || count($producto_existe)>0 ){
            //     $data =array(
            //         'status' => 'error',
            //         'code' => 404,
            //         'message' => 'El producto ya esta registrado.',
            //         'errors' => $validate->errors(),
            //         'producto'  =>  $producto_existe
            //     );
            // }else{

                $producto = new Inventario();

                $producto->article_id = $params_array['article_id'];
                $producto->date = $params_array['date'];
                $producto->description = $params_array['description'];
                $producto->entrada = $params_array['entrada'];
                $producto->ent_val_uni = $params_array['ent_val_uni'];
                $producto->ent_val_tot = $params_array['ent_val_uni'] * $params_array['entrada'];
                $producto->salida = 0;
                $producto->sal_val_uni = 0;
                $producto->sal_val_tot = 0;
                $producto->existe = $producto_existe != null ? $producto_existe[0]->existe + $params_array['entrada'] : $params_array['entrada'];
                $producto->exi_val_tot = $producto_existe == null ? $producto->ent_val_tot : $producto->ent_val_tot + $producto_existe[0]->exi_val_tot;
                $producto->exi_val_uni = round($producto->exi_val_tot / $producto->existe,2);
                $producto->estado = $params_array['estado'];

                $producto->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El producto se ha creado correctamente.',
                    'producto' => $producto
                );
            // }
        }else{
            $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Los datos enviados no son correctos'
                );
        }

        return response()->json($data);
    }

    public function listar(){
        $sql = 'SELECT 
        inventory.id,
        articles.name,
        articles.code,
        inventory.`date`,
        inventory.description,
        inventory.entrada,
        inventory.ent_val_uni,
        inventory.ent_val_tot,
        inventory.salida,
        inventory.sal_val_uni,
        inventory.sal_val_tot,
        inventory.existe,
        inventory.exi_val_uni,
        inventory.exi_val_tot,
        inventory.estado
      FROM
        articles
        INNER JOIN inventory ON (articles.id = inventory.article_id)';
        $estado_eliminado = DB::select($sql);
    
        return response()->json($estado_eliminado,200);
        // $total = Inventario::all();
        // return response()->json($total,200);
    }

    public function listarInventario(){
        $sql = 'SELECT 
        inventory.id,
        articles.name,
        articles.code,
        IF(inventory.description = "DISPONIBLE" || inventory.description = "AGREGAR",(inventory.entrada - inventory.entrada),inventory.entrada ) AS devuelto,
        inventory.salida AS alquiler,
        inventory.existe AS totalDisponible
      FROM
        articles
        INNER JOIN inventory ON (articles.id = inventory.article_id)
      WHERE
        inventory.estado = 1';
        $inventario = DB::select($sql);
    
        return response()->json($inventario,200);
        // $total = Inventario::all();
        // return response()->json($total,200);
    }

    public function eliminarProducto($id){
        $sql = 'DELETE FROM inventory where id = '.$id;
        $estado_eliminado = DB::select($sql);

        return response()->json($estado_eliminado,200);
    }

    // ******************************************************
    public function alquiler(Request $request,$facturaID){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array

        if(!empty($params) && !empty($params_array)){
            //Limpiar datos de espacios de los parametros
            // $params_array = array_map('trim',$params_array);

            //validar datos
            $validate = \Validator::make($params_array,[
                'article_id' => 'required',
                'date' => 'required',
                'description' => 'required',
                'salida' => 'required',
                'sal_val_uni' => 'required',
                'estado' => 'required',
            ]);
            

            $sql = "SELECT * FROM inventory WHERE article_id = {$params_array['article_id']} and estado =1 ORDER by id DESC";
            $producto_existe = DB::select($sql);

            if(count($producto_existe) > 0){
                $sql2 = "UPDATE inventory SET estado = 0 WHERE id = {$producto_existe[0]->id}";
                DB::select($sql2);
            }

            if($validate->fails() ){
                $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El producto ya esta registrado.',
                    'errors' => $validate->errors(),
                );
            }else{

                $producto = new Inventario();

                $producto->article_id = $params_array['article_id'];
                $producto->date = $params_array['date'];
                $producto->description = $params_array['description'];
                $producto->entrada = 0;
                $producto->ent_val_uni = 0;
                $producto->ent_val_tot = 0;
                $producto->salida = $params_array['salida'];
                $producto->sal_val_uni = $producto_existe[0]->exi_val_uni;
                $producto->sal_val_tot = round($producto_existe[0]->exi_val_uni * $params_array['salida'],2);
                $producto->existe = $producto_existe != null ? $producto_existe[0]->existe - $params_array['salida'] : $params_array['salida'];
                $producto->exi_val_tot = $producto_existe == null ? $producto->sal_val_tot : round($producto_existe[0]->exi_val_tot - $producto->sal_val_tot,2);
                $producto->exi_val_uni = $producto->existe == 0 ? $producto_existe[0]->exi_val_uni : round($producto->exi_val_tot / $producto->existe,2);
                $producto->estado = $params_array['estado'];

                $producto->save();

                $facturaDetalle = new Factura_Detalle();

                $facturaDetalle->invoice_id = $facturaID;
                $facturaDetalle->article_id = $params_array['article_id'];
                $facturaDetalle->quantity = $params_array['salida'];
                $facturaDetalle->val_uni = $params_array['sal_val_uni'];
                $facturaDetalle->val_tot = $facturaDetalle->quantity * $facturaDetalle->val_uni;

                $facturaDetalle->save();

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El producto se ha creado correctamente.',
                    'producto' => $producto
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

    public function disfracesDisponibles(){
        $sql = "SELECT 
        articles.id,
        articles.name,
        articles.code,
        articles.price,
        inventory.existe,
        inventory.exi_val_uni,
        inventory.exi_val_tot
      FROM
        articles
        INNER JOIN inventory ON (articles.id = inventory.article_id)
      WHERE
        inventory.estado = 1
        ";

        $articulosDisponibles = DB::select($sql);

        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El producto se ha creado correctamente.',
            'articulosDisponibles' => $articulosDisponibles
        );

        return response()->json($data,$data['code']);

    }

    public function articuloDisponible($articulo){
        $sql = "SELECT 
        articles.id,
        articles.name,
        articles.code,
        articles.price,
        inventory.existe,
        inventory.exi_val_uni,
        inventory.exi_val_tot
      FROM
        articles
        INNER JOIN inventory ON (articles.id = inventory.article_id)
        INNER JOIN articles_costume ON (articles.id = articles_costume.article_id)
        INNER JOIN costumes ON (articles_costume.costume_id = costumes.id)
      WHERE
        inventory.estado = 1 and
        articles.id = {$articulo}
        ";

        $articulosDisponibles = DB::select($sql);

        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'El producto se ha creado correctamente.',
            'articulosDisponibles' => $articulosDisponibles
        );

        return response()->json($data,$data['code']);

    }

    // **********************************************************

    public function devolver(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array

        if(!empty($params) && !empty($params_array)){
            //Limpiar datos de espacios de los parametros
            // $params_array = array_map('trim',$params_array);

            //validar datos
            $validate = \Validator::make($params_array,[
                'article_id' => 'required',
                'date' => 'required',
                'description' => 'required',
                'entrada' => 'required',
            ]);

            $sql = "SELECT * FROM inventory WHERE article_id = {$params_array['article_id']} and estado =1 ORDER by ID DESC";
            $producto_existe = DB::select($sql);

            if(count($producto_existe) > 0){
                $sql2 = "UPDATE inventory SET estado = 0 WHERE id = {$producto_existe[0]->id}";
                DB::select($sql2);
            }

            // if($validate->fails() || count($producto_existe)>0 ){
            //     $data =array(
            //         'status' => 'error',
            //         'code' => 404,
            //         'message' => 'El producto ya esta registrado.',
            //         'errors' => $validate->errors(),
            //         'producto'  =>  $producto_existe
            //     );
            // }else{

            $sql3 = "SELECT 
                    inventory.id,
                    inventory.article_id,
                    inventory.date,
                    inventory.description,
                    inventory.entrada,
                    inventory.exi_val_uni,
                    articles.name,
                    articles.code
                FROM
                    articles
                    INNER JOIN inventory ON (articles.id = inventory.article_id)
                WHERE
                    inventory.estado = 0 AND 
                    inventory.description = 'DEVOLUCION' AND
                    inventory.article_id = {$params_array['article_id']}
                    OR 
                    inventory.estado = 1 AND 
                    inventory.description = 'DEVOLUCION' AND
                    inventory.article_id = {$params_array['article_id']}";
            $articulosDevueltos = DB::select($sql3);

            // cambiar estado para saber los articulos devueltos
            if(count($articulosDevueltos)>0){
                $sql4 = "UPDATE inventory SET estado = 2 WHERE id = {$articulosDevueltos[0]->id}";
                DB::select($sql4);
            }

                $producto = new Inventario();

                $producto->article_id = $params_array['article_id'];
                $producto->date = $params_array['date'];
                $producto->description = $params_array['description'];
                $producto->entrada = count($articulosDevueltos)>0 ? $articulosDevueltos[0]->entrada + $params_array['entrada'] : $params_array['entrada'];
                $producto->ent_val_uni = 0;
                $producto->ent_val_tot = 0;
                $producto->salida = 0;
                $producto->sal_val_uni = 0;
                $producto->sal_val_tot = 0;
                $producto->existe = $producto_existe[0]->existe;
                $producto->exi_val_tot = $producto_existe[0]->exi_val_tot;
                $producto->exi_val_uni = $producto_existe[0]->exi_val_uni;
                $producto->estado = $params_array['quantity'] - $params_array['entrada'] != 0 ? 0 : 1;

                $producto->save();

                // ********************
                if($params_array['quantity'] - $params_array['entrada'] != 0){
                    $productoVendido = new Inventario();

                    $productoVendido->article_id = $params_array['article_id'];
                    $productoVendido->date = $params_array['date'];
                    $productoVendido->description = 'VENTA';
                    $productoVendido->entrada = 0;
                    $productoVendido->ent_val_uni = 0;
                    $productoVendido->ent_val_tot = 0;
                    $productoVendido->salida = $params_array['quantity'] - $params_array['entrada'];
                    $productoVendido->sal_val_uni = $producto_existe[0]->exi_val_uni;
                    $productoVendido->sal_val_tot = $productoVendido->salida * $productoVendido->sal_val_uni;
                    $productoVendido->existe = $producto_existe[0]->existe;
                    $productoVendido->exi_val_uni = $producto_existe[0]->exi_val_uni;
                    $productoVendido->exi_val_tot = $productoVendido->existe * $productoVendido->exi_val_uni;
                    $productoVendido->estado = 1;

                    $productoVendido->save();
                }
                
                // ********************

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El producto se ha creado correctamente.',
                    'producto' => $producto
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

    // ************************************************************

    public function articulosDevueltos(){

        $sql = "SELECT 
                    inventory.article_id,
                    inventory.date,
                    inventory.description,
                    inventory.entrada,
                    inventory.exi_val_uni,
                    articles.name,
                    articles.code
                FROM
                    articles
                    INNER JOIN inventory ON (articles.id = inventory.article_id)
                WHERE
                    inventory.estado = 0 AND 
                    inventory.description = 'DEVOLUCION'
                    OR 
                    inventory.estado = 1 AND 
                    inventory.description = 'DEVOLUCION'";
            $articulosDevueltos = DB::select($sql);

            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Articulos devueltos.',
                'articulos' => $articulosDevueltos
            );      

        return response()->json($data,$data['code']);
    }

    public function articuloDevuelto($article){

        $sql = "SELECT 
                inventory.id,
                inventory.article_id,
                inventory.date,
                inventory.description,
                inventory.entrada as maximo,
                inventory.exi_val_uni,
                articles.name,
                articles.code
            FROM
                articles
                INNER JOIN inventory ON (articles.id = inventory.article_id)
            WHERE
                inventory.estado = 0 AND 
                inventory.description = 'DEVOLUCION' AND 
                inventory.article_id = {$article} OR 
                inventory.estado = 1 AND 
                inventory.description = 'DEVOLUCION' AND 
                inventory.article_id = {$article}";
        $articuloDevuelto = DB::select($sql);

        if(count($articuloDevuelto) > 0){
            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Articulo devuelto.',
                'articulo' => $articuloDevuelto[0]
            ); 
        }else{
            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Articulo devuelto.',
                'articulo' => []
            ); 
        }
           

        return response()->json($data,$data['code']);
    }

    public function habilitar(Request $request){

        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array
        
        // buscar articulo en inventario para saber el precio
        $sql2 = "SELECT * FROM inventory WHERE article_id = {$params_array['article_id']} and estado =1 ORDER BY ID DESC";
        $producto_existe = DB::select($sql2);

        // cambiar estado
        $sql = "UPDATE inventory SET estado = 2 WHERE id = {$producto_existe[0]->id}";
        DB::select($sql);

        $sql3 = "SELECT 
                    inventory.id,
                    inventory.article_id,
                    inventory.date,
                    inventory.description,
                    inventory.entrada,
                    inventory.exi_val_uni,
                    articles.name,
                    articles.code
                FROM
                    articles
                    INNER JOIN inventory ON (articles.id = inventory.article_id)
                WHERE
                    inventory.estado = 0 AND 
                    inventory.description = 'DEVOLUCION' AND
                    inventory.article_id = {$params_array['article_id']}
                    OR 
                    inventory.estado = 1 AND 
                    inventory.description = 'DEVOLUCION' AND
                    inventory.article_id = {$params_array['article_id']}";
            $articulosDevueltos = DB::select($sql3);

            // cambiar estado para saber los articulos devueltos
            if(count($articulosDevueltos)>0){
                $sql4 = "UPDATE inventory SET estado = 2 WHERE id = {$articulosDevueltos[0]->id}";
                DB::select($sql4);
            }


        // insertar nuevo 
        $productoDisponible = new Inventario();
        
        $productoDisponible->article_id = $params_array['article_id'];
        $productoDisponible->date = $params_array['date'];
        $productoDisponible->description = $params_array['description'];
        $productoDisponible->entrada = $params_array['entrada'];
        $productoDisponible->ent_val_uni = $producto_existe[0]->exi_val_uni;
        $productoDisponible->ent_val_tot = round($params_array['entrada'] * $producto_existe[0]->exi_val_uni,2);
        $productoDisponible->salida = 0;
        $productoDisponible->sal_val_uni = 0;
        $productoDisponible->sal_val_tot = 0;
        $productoDisponible->existe = $producto_existe[0]->existe + $params_array['entrada'];
        $productoDisponible->exi_val_tot = round($producto_existe[0]->exi_val_tot + $productoDisponible->ent_val_tot ,2);
        $productoDisponible->exi_val_uni = round($productoDisponible->exi_val_tot / $productoDisponible->existe,2);
        $productoDisponible->estado = $params_array['maximo'] - $params_array['entrada'] != 0 ? 2 : 1;

        $productoDisponible->save();

        $producto = new Inventario();

        if($params_array['maximo'] - $params_array['entrada'] != 0){
            $producto->article_id = $params_array['article_id'];
            $producto->date = $params_array['date'];
            $producto->description = 'DEVOLUCION';
            $producto->entrada = $params_array['maximo'] - $params_array['entrada'];
            $producto->ent_val_uni = 0;
            $producto->ent_val_tot = 0;
            $producto->salida = 0;
            $producto->sal_val_uni = 0;
            $producto->sal_val_tot = 0;
            $producto->existe = $productoDisponible->existe;
            $producto->exi_val_tot = $productoDisponible->exi_val_tot;
            $producto->exi_val_uni = $productoDisponible->exi_val_uni;
            $producto->estado = 1;

            $producto->save();
        }
        

        

        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'Articulo disponible.',
            'productoDisponibles' => $productoDisponible,
            'productosDevueltos' => $producto
        );    

        return response()->json($data,$data['code']);
    }

    // ***************REPORTES***************************************

    public function reporteUsuariosFecha($desde, $hasta){
        $sql = "SELECT 
                invoice.date,
                IF(rentals.devuelto > 0, 'SI','NO') as devuelto,
                customers.id,
                customers.name,
                customers.surname,
                customers.identity_card,
                customers.direction,
                customers.cellphone,
                customers.email
            FROM
                invoice
                INNER JOIN rentals ON (invoice.id = rentals.invoice_id)
                INNER JOIN customers ON (invoice.customer_id = customers.id)
            WHERE
                invoice.date
            BETWEEN 
                {$desde}
            AND
                {$hasta}";
        $usuariosAlquiler = DB::select($sql);

        if(count($usuariosAlquiler) >0){
            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Lista de clientes alquiler.',
                'data' => $usuariosAlquiler
            );    
        }else{
            $data =array(
                'status' => 'error',
                'code' => 100,
                'message' => 'No hay clientes de alquiler en el rago de fecha.',
                'data' => $usuariosAlquiler
            );  
        }
        return response()->json($data);
    }
}
