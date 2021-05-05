<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//rutas de usuarios
Route::post('/register','UserController@register');
Route::post('/login','UserController@login');
Route::get('/usuarios','UserController@totalUsuarios');
Route::get('/usuario/{id}','UserController@usuario');
Route::delete('/borrar/usuario/{id}','UserController@borrarUsuario');
Route::get('/roles/usuario/{id}','UserController@getRolesUsuario');
Route::delete('/quitar/rol/usuario/{usuarioId}/{rolId}','UserController@borrar_roles_usuario');
Route::post('/agregar/rol/usuario/{usuarioId}/{rolId}','UserController@agregarRolUsuario');

//administracion
Route::put('/user/update','UserController@update');



//rutas de categorias
Route::get('/categorias','CategoryController@listar');
Route::post('/register/categoria','CategoryController@register');
Route::delete('/borrar/categoria/{id}','CategoryController@borrarCategoria');
Route::put('/actulizar/categoria','CategoryController@actualizarCategoria');
Route::get('/categoria/{id}','CategoryController@getCategoria');

//rutas disfraces
Route::get('/disfraces','DisfrazController@listar');
Route::post('/register/disfraz','DisfrazController@register');
Route::delete('/borrar/disfraz/{id}','DisfrazController@borrarDisfraz');
Route::put('/actulizar/disfraz','DisfrazController@actualizarDisfraz');
Route::get('/disfraz/{id}','DisfrazController@getDisfraz');
Route::post('/subir/foto/disfraz','DisfrazController@guardarImagenServidor');
Route::put('/actualizar/foto/disfraz/','DisfrazController@actualizarFoto');
Route::put('/actualizar/foto/disfraz/probador','DisfrazController@actualizarFotoProbador');
Route::get('/disfraz/foto/{filename}','DisfrazController@getImage');
Route::get('/categorias/disfraz/{id}','DisfrazController@mostrarCategoriasDisfraz');
Route::post('/agregar/categoria/disfraz/{disfrazId}/{categoriaId}','DisfrazController@agregarCategoria');
Route::delete('/quitar/categoria/disfraz/{disfrazId}/{categoriaId}','DisfrazController@quitarCategoria');
Route::get('/articulos/disfraz/{id}','DisfrazController@mostrarArticulosDisfraz');
Route::post('/agregar/articulo/disfraz/{disfrazId}/{categoriaId}','DisfrazController@agregarArticulo');
Route::delete('/quitar/articulo/disfraz/{disfrazId}/{categoriaId}','DisfrazController@quitarArticulo');


// rutas articulos
Route::get('/articulos','ArticuloController@listar');
Route::post('/register/articulo','ArticuloController@register');
Route::delete('/borrar/articulo/{id}','ArticuloController@borrarArticulo');
Route::put('/actulizar/articulo','ArticuloController@actualizarArticulo');
Route::get('/articulo/{id}','ArticuloController@getArticulo');

// roles
Route::get('/roles','UserController@totalRoles');
Route::get('/rol/{id}','UserController@getRol');
Route::post('/agregar/rol/{rol}','UserController@agregarRol');
Route::put('/rol/update','UserController@actualizarRol');
Route::delete('/borrar/rol/{id}','UserController@borrarRol');

// Garantia
Route::get('/garantias','GarantiaController@listar');
Route::get('/garantia/{id}','GarantiaController@getGarantia');
Route::post('/register/garantia','GarantiaController@register');
Route::put('/actualizar/garantia','GarantiaController@actualizarGarantia');
Route::delete('/borrar/garantia/{id}','GarantiaController@borrarGarantia');

// inventario
Route::get('/productos','InventarioController@listar');
Route::post('/register/producto','InventarioController@register');
Route::delete('/borrar/producto/{id}','InventarioController@eliminarProducto');

Route::get('/ver/inventario','InventarioController@listarInventario');


// alquiler
Route::post('/alquilar/{facturaID}','InventarioController@alquiler');
Route::get('/articulos/disponibles','InventarioController@disfracesDisponibles');
Route::get('/articulo/disponible/{id}','InventarioController@articuloDisponible');

// factura
Route::post('/facturar','FacturaController@registrar');
Route::post('/venta','FacturaController@venta');
Route::post('/ventaArticulos/{facturaID}','FacturaController@ventaArticulos');

//devoluciones
Route::get('/devolucion/cliente/{cedula}','FacturaController@getClienteAlquiler');
Route::post('/devolucion/articulo','InventarioController@devolver');
Route::post('/devolucion/cliente/{id}','FacturaController@quitarClienteAlquiler');

// disponibilidad
Route::get('/articulos/devueltos','InventarioController@articulosDevueltos');
Route::get('/articulos/devueltos/{article}','InventarioController@articuloDevuelto');
Route::post('/habilitar/articulo','InventarioController@habilitar');


// clientes
Route::get('/cliente/{cedula}','CustomerController@getCliente');
Route::post('/ingresar/cliente','CustomerController@setCliente');

// Reportes
Route::get('/reportes/clientes/{desde}/{hasta}','InventarioController@reporteUsuariosFecha');


// web
Route::get('/categoria/disfraces/{id}','DisfrazController@categoriaDisfraces');

// IVA
Route::get('/iva','IvaController@getIva');
Route::get('/ivas','IvaController@getIvas');
Route::post('/nuevo/iva/{iva}','IvaController@setIva');
Route::post('/activar/iva/{idIva}','IvaController@setIvas');




