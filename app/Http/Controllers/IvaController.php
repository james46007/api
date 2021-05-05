<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Iva;
use Illuminate\Support\Facades\DB;

class IvaController extends Controller
{
    public function getIva(){
        // $iva = Iva::where('estado',1)->first();       
        $sql = "SELECT * FROM iva WHERE estado = 1";
        $total = DB::select($sql);

        return response()->json($total[0],200);
    }

    public function getIvas(){
        // $iva = Iva::where('estado',1)->first();       
        $sql = "SELECT * FROM iva";
        $total = DB::select($sql);

        return response()->json($total,200);
    }

    public function setIva($iva){
        $nuevoIva = new Iva();   
        $nuevoIva->iva = $iva;
        $nuevoIva->estado = 0;
        $nuevoIva->save();
        // $sql = "SELECT * FROM iva WHERE estado = 1";
        // $total = DB::select($sql);
        $data =array(
            'status' => 'success',
            'code' => 200,
            'message' => 'La garantia se ha creado correctamente.',
            'iva' => $nuevoIva,
        );

        return response()->json($data,200);
    }

    public function setIvas($iva){
        // $iva = Iva::where('estado',1)->first();       
        $sql = "UPDATE iva SET estado = 0";
        $total = DB::select($sql);

        $sql = "UPDATE iva SET estado = 1 WHERE id = {$iva}";
        $total = DB::select($sql);

        return response()->json($total,200);
    }
}
