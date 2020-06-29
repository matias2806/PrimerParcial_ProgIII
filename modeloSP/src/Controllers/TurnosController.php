<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Utils\AutentificadorJWT;
use App\Models\Usuario;
use App\Models\Mascota;
use App\Models\Turno;
use App\Utils\Re;


class TurnosController {

    public function add(Request $request, Response $response, $args)
    {
        $req= $request->getParsedBody();
        $dataToken = AutentificadorJWT::ObtenerData($request->getHeader('token')[0]);
        $masc = new Mascota();
        $user =new Usuario();
        $turno = new Turno();

        $fecha = \DateTime::createFromFormat('j-m-Y', $req['fecha']);
        $hora = \DateTime::createFromFormat('H:i', $req['hora']);

        $turno->id_mascota = $req['id_mascota'];
        $turno->fecha = $fecha;
        $turno->hora = $hora;
        $turno->id_veterinario = $req['id_veterinario'];
        $rta=$turno;

        $checkMasc = $masc->where('id_mascota', $turno->id_mascota)
                ->where('id_cliente', $dataToken->id)
                ->first();

        $checkVete = $user->where('id', $turno->id_veterinario)
            ->where('tipo', 'veterinario')
            ->first();

        $checkTurn = $turno->where('fecha', $fecha->format('Y-m-d'))
            ->where('hora', $hora->format('H:i'))
            ->where('id_veterinario', $turno->id_veterinario)
            ->first();

        if (!empty($checkMasc)) {
            if (!empty($checkVete)) {
                if (empty($checkTurn)) {
                    $turno->save();
                    $rta = Re::Respuesta(1, "Turno registrado exitosamente");
                } else {

                    $rta = Re::Respuesta(0, "Turno no disponible con el veterinario " . $turno->id_veterinario);
                }
            } else {
                $rta = Re::Respuesta(0, "Veterinario no registrado");

            }
        } else {
            $rta = Re::Respuesta(0, "Mascota no registrada");
        }


        $response->getBody()->write($rta);

        return $response
        ->withHeader('Content-Type','application/json');
    }
}