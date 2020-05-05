<?php
include_once "archivos.php";

class Usuario{


    public $email;
    public $clave;
    public $tipo;
 

    public function __construct($email, $clave, $tipo){

        $this->email = $email;
        $this->clave = $clave;
        $this->tipo = $tipo;
  
    }

    public function VerDatos(){
        echo
        json_encode($this);
    }


    public function save($nombreArchivo) {
        // return Datos::guardar('datos.txt', $this->toFile());
        return Archivos::guardarJSON($nombreArchivo, $this);
    }

}




?>