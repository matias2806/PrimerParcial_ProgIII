<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Utils\AutentificadorJWT;
use App\Models\Usuario;
use App\Models\Mascota;
use App\Utils\Re;


class MascotaController {

    public function add(Request $request, Response $response, $args)
    {
        $req= $request->getParsedBody();
        $masc = new Mascota();
        $dataToken = AutentificadorJWT::ObtenerData($request->getHeader('token')[0]);
        
        $masc->nombre=$req['nombre'];
        $masc->edad=$req['edad'];
        $masc->id_cliente =  $dataToken->id;

        $check = $masc->where('nombre', $masc->nombre)
                ->where('id_cliente', $masc->id_cliente)
                ->first();
            if (empty($check)) {
                $masc->save();
                $rta = Re::Respuesta(1, "Mascota registrada exitosamente");
            } else {
                $rta = Re::Respuesta(0, "Mascota ya registrada");
            }

        $response->getBody()->write($rta);
       //$response->getBody()->write($dataToken[0]);

        return $response
        ->withHeader('Content-Type','application/json');
    }
}