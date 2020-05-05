<?php

class Response{
    public static function Respuesta($id, $mensage)
    {
        $status = "";
        $retorno = new stdClass();
        switch ($id) {
            case -1:
                $status = "Error";
                break;
            case 0:
                $status = "Fallo";
                break;
            case 1:
                $status = "Exito";
                break;
            default:
                $status ="Sin estado";
                break;
        }
        $retorno->status = $status;
        $retorno->message = $mensage;
        return json_encode($retorno);
    }
}