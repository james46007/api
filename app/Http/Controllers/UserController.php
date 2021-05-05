<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Rol;
use App\User_Roles;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth',['except' => 
        ['getImage',
        'detail',
        'update',
        'register',
        'login',
        'getRolesUsuario',
        'totalUsuarios',
        'totalRoles',
        'borrarUsuario',
        'usuario',
        'borrar_roles_usuario',
        'agregarRolUsuario',
        'borrarRol',
        'agregarRol',
        'getRol',
        'actualizarRol',
        ]

        ]);
    }

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
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email',
                'password' => 'required'
            ]);

            $user_exit = User::where('email', $params_array['email'])->first();
            
            
            if($validate->fails() || !empty($user_exit) ){
                $data =array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'El correo electronico ya esta registrado.',
                    'errors' => $validate->errors(),
                    'user'  =>  $user_exit
                );
            }else{

                $pwd = hash('sha256',$params->password);

                $user = new User();


                $user->name = strtoupper($params_array['name']);
                $user->surname = strtoupper($params_array['surname']);
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->estado = 1;

                $user->save();

                // for ($i = 0; $i < count($params_array['roles']); $i++) {
                //     $userRol = new User_Roles();
                //     $userRol->user_id = $user->id;
                //     $userRol->rol_id = $params_array['roles'][$i];
                //     $userRol->save();
                // }
                

                $data =array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'El usuario se ha creado correctamente.',
                    'user' => $user
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

    public function login(Request $request){
        $jwtAuth = new \JwtAuth();

        $json = $request->input('json',null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);
        $validate = \Validator::make($params_array,[
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validate->fails()){
            $signup =array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no pudo identificarse',
                'errors' => $validate->errors()
            );
        }else{
            $pwd = hash('sha256',$params->password);

            $signup = $jwtAuth->signup($params->email,$pwd);
            if(!empty($params->gettoken)){
                $signup = $jwtAuth->signup($params->email,$pwd,true);
            }
        }

        return response()->json($signup,200);
    }

    public function totalUsuarios(){
        // $usuariosTotal = User::all();
        $sql = 'SELECT * FROM users  where estado=1';
        $usuariosTotal = DB::select($sql);
        return response()->json($usuariosTotal,200);
    }

    public function update(Request $request){
        $token = $request->header('Authorization');

        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //recoger los datos del post
        $json = $request->input('json',null);
        $params_array = json_decode($json, true);

        // if($checkToken && !empty($params_array)){
        if(!empty($params_array)){


            $user = $jwtAuth->checkToken($token,true);

            $id = $params_array['id'];

            
            try {
                $validate = \Validator::make($params_array,[
                    'name' => 'required|alpha',
                    'surname' => 'required|alpha',
                    'email' => 'required|email|unique:users,'.$id
                ]);

                $rolesCambio = $params_array['roles'];

                //quitar los datos que no quiero actualizar
                unset($params_array['id']);
                unset($params_array['password']);
                unset($params_array['create_at']);
                unset($params_array['remenber_token']);
                unset($params_array['roles']);

                //actualizar el usuario en la bbd
                $user_update = User::where('id',$id)->update($params_array);

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'user' => $user_update,
                    'changes' => $params_array,
                );
            } catch (\Throwable $th) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El correo ya esta registrado con otro usuario.',
                );
                return $data;
            }         

        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuari no esta identificado.'
            );
        }
        return response()->json($data,$data['code']);
    }

    public function upload(Request $request){
        //recoger archivos
        $image = $request->file('file0');

        $validate = \Validator::make($request->all(), [
            'file0' => 'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        if(!$image || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir imagen.'
            );
        }else{
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('users')->put($image_name, \File::get($image));
            $data = array(
                'code' => 200,
                'status' => 'success',
                'image' => $image_name
            );
        }

        return response($data,$data['code'])->header('Content-Type','text/plain');
    }

    public function getImage($filename){
        $isset = \Storage::disk('users')->exists($filename);

        if($isset){
            $file = \Storage::disk('users')->get($filename);
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

    public function borrarUsuario($id){
        $sql = 'UPDATE users SET estado=0 where id = '.$id;
        // $sql = 'DELETE FROM users where id = '.$id;
        $usuario_eliminado = DB::select($sql);

        return response()->json($usuario_eliminado,200);
    }

    // ****************************

    public function detail($id){
        $user = User::find($id);

        if(is_object($user)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'user' => $user
            );
        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El usuario no existe.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function usuario($id){
        $sql = 'SELECT id,name,surname,email,estado FROM users where id = '.$id;
        $usuario = DB::select($sql);

        $roles = $sql = 'SELECT rol_id FROM user_roles where user_id = '.$id;
        $roles_usuario = DB::select($sql);

        $data =array(
            'roles' => $roles_usuario,
            'user'  =>  $usuario
        );

        return response()->json($data,200);
    }

    // ************

    public function totalRoles(){
        $sql = 'SELECT id,name FROM roles';
        $roles_total = DB::select($sql);

        return response()->json($roles_total,200);
    }

    public function getRolesUsuario($id){
        $sql = 'SELECT 
        roles.name,
        user_roles.id
      FROM
        roles
        INNER JOIN user_roles ON (roles.id = user_roles.rol_id)
        INNER JOIN users ON (user_roles.user_id = users.id)
      WHERE users.id = '.$id.' AND user_roles.estado = 1';
        $roles_usuario = DB::select($sql);

        return $roles_usuario;
    }

    public function borrar_roles_usuario($usuarioId, $rolId){
        $sql = 'UPDATE user_roles SET estado=0 where id='.$rolId;
        $categoria_eliminado = DB::select($sql);

        return response()->json($categoria_eliminado,200);
    }

    public function agregarRolUsuario($usuarioId, $rolId){
        $data =array(
            'status' => 'error',
            'code' => 404,
            'message' => 'Los datos enviados no son correctos'
        );

        $sql = "SELECT * FROM user_roles WHERE user_id = ".$usuarioId." and rol_id = ".$rolId;
        $disfraz_existe = DB::select($sql);

        if(count($disfraz_existe)>0 ){
            $data =array(
                'status' => 'error',
                'code' => 200,
                'message' => 'El rol ya esta registrado con el usuario.',
                'categoria'  =>  $disfraz_existe
            );
        }else{

            $usuarioRoles = new User_Roles();     

            $usuarioRoles->user_id = $usuarioId;
            $usuarioRoles->rol_id = $rolId;
            $usuarioRoles->estado = 1;

            $usuarioRoles->save();

            $data =array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El rol se registro correctamente con el usuario.',
                'categorias' => $usuarioRoles,
            );
        }

        return response()->json($data,$data['code']);
    }

    public function borrarRol($id){
        $rolEliminado = Rol::find($id);        
        $rolEliminado->delete();

        return response()->json($rolEliminado,200);
    }

    public function agregarRol($rol){

        $sql = "SELECT * FROM roles WHERE name = '".$rol."'";
        $rol_existe = DB::select($sql);

        if(count($rol_existe)>0 ){
            $data = array(
                'code' => 200,
                'status' => 'error',
                'message' => 'El rol ya existe.'
            );
        }else{
            $nuevoRol = new Rol();
            $nuevoRol->name = $rol;
            $nuevoRol->save();
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'El rol se creo correctamente.'
            );
        }        

        return response()->json($data,200);
    }

    public function getRol($id){
        $sql = "SELECT * FROM roles WHERE id = '".$id."'";
        $rol_existe = DB::select($sql);

        if(count($rol_existe)>0 ){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'rol' => $rol_existe[0]
            );
        }else{
            $nuevoRol = new Rol();
            $nuevoRol->name = $rol;
            $nuevoRol->save();
            $data = array(
                'code' => 200,
                'status' => 'error',
                'message' => 'El rol no existe.'
            );
        }        

        return response()->json($data,200);
    }

    public function actualizarRol(Request $request){
        //Recoge los datos del usuario por post
        $json = $request->input('json',null);
        $params = json_decode($json);//objeto
        $params_array = json_decode($json,true);//array

        if(!empty($params_array)){

            $id = $params_array['id'];

            
            try {
                //validar datos
                $validate = \Validator::make($params_array,[
                    'name' => 'required|alpha'
                ]);

                $user_exit = Rol::where('name', $params_array['name'])->first();
            
            
                if($validate->fails() || !empty($user_exit) ){
                    $data =array(
                        'status' => 'error',
                        'code' => 404,
                        'message' => 'El rol ya esta registrado.',
                        'errors' => $validate->errors(),
                        'user'  =>  $user_exit
                    );
                }else{
                    //quitar los datos que no quiero actualizar
                    unset($params_array['id']);
                    unset($params_array['create_at']);

                    //actualizar el usuario en la bbd
                    $user_update = Rol::where('id',$id)->update($params_array);

                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'El rol ya esta registrado.',
                        'changes' => $params_array,
                    );
                }                
                
            } catch (\Throwable $th) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El rol ya esta registrado.',
                );
                return $data;
            }         

        }else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error vuelva a intentarlo mas tarde.'
            );
        }
        return response()->json($data,$data['code']);
    }

}
