<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Utils\AutentificadorJWT;
use App\Models\Usuario;
use App\Utils\Re;
use Slim\Routing\RouteContext;

class UsuariosController {

    public function getAll(Request $request, Response $response, $args)
    {
        $rta = json_encode(Usuario::all());
        $response->getBody()->write($rta);

        return $response
        ->withHeader('Content-Type','application/json');
    }

    public function getId(Request $request, Response $response, $args)
    {
        $contenido = RouteContext::fromRequest($request);
        $ruta = $contenido->getRoute();
        $datoId = $ruta->getArgument('id');


        $usuario =  Usuario::find($datoId);
        
        
        $usuario->save();

        $rta = json_encode($usuario);
        
        $response->getBody()->write($rta);

        return $response
        ->withHeader('Content-Type','application/json');
    }

    public function add(Request $request, Response $response, $args)
    {
        $req= $request->getParsedBody();
        $user = new Usuario();
        
        $user->tipo=$req['tipo'];
        $user->mail=$req['mail'];
        $user->clave = password_hash( $req['clave'], PASSWORD_BCRYPT);
       
       $flag = $user->where('mail',$user->mail)->first();
       
       if(empty($flag) && ($user->tipo == "cliente" || $user->tipo=='veterinario')){
            $user->save();
            $rta = Re::Respuesta(1, "Usuario Cargado" );
       }else{
          
            $rta = Re::Respuesta(0, "Mail ya registrado o tipo de usuario invalido");
       }

        $response->getBody()->write($rta);

        return $response
        ->withHeader('Content-Type','application/json');
    }

    public function login(Request $request, Response $response, $args)
    {
        $req= $request->getParsedBody();
        $user = new Usuario();
        $user->mail=$req['mail'];
        $datoClave=$req['clave'];

        $selec = $user->where('mail',$user->mail)->first();
        if(!empty($selec)){
            $hasheo = $selec->clave;
            if(password_verify($datoClave, $hasheo)){
                $Objeto = new \stdClass();

                $Objeto->id = $selec->id;
                $Objeto->mail = $selec->mail;
                $Objeto->tipo = $selec->tipo;

                $rta = Re::Respuesta(1, "Token: ".AutentificadorJWT::CrearToken($Objeto));

            }else{
                $rta = Re::Respuesta(0,"clave incorrecta");
            }

        }
        else{
            $rta = Re::Respuesta(0,"Mail no registrado");
        }


        $response->getBody()->write($rta);

        return $response
        ->withHeader('Content-Type','application/json');
    }



}
