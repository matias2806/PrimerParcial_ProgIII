<?php

include_once "src/archivos.php";
include_once "src/Response.php";
include_once "src/usuario.php";
include_once "autenticadoJWT.php";
use  App\Models\AutentificadorJWT;

$path = $_SERVER['PATH_INFO'];



switch($path){
    case '/usuario':
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if  (isset($_POST['email']) == true && isset($_POST['clave']) == true && isset($_POST['tipo']) == true)
            {
                $email = $_POST['email'];
                $clave = $_POST['clave'];
                $tipo = $_POST['tipo'];
                $user = new Usuario($email, $clave, $tipo);

                Archivos::GuardarJSON("data/users.json", $user);

                echo Response::respuesta(1, "Usuario agregado");
            }
            else
            {
                echo Response::respuesta(0, "Agregue las key que faltan: email clave y tipo ");
            }
        }else{
            echo Response::respuesta(0, "Debe ser un POST");
        }
        break;
    case '/login':

        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            if  (isset($_POST['email']) == true && isset($_POST['clave']) == true)
            {
                $email = $_POST['email'];
                $clave = $_POST['clave'];

                $listadoUsuarios = Archivos::LeeJson("data/users.json");
                $flag = false;
                foreach($listadoUsuarios as $usuario){

                    if($email == $usuario->email && $clave == $usuario->clave )
                    {
                        $tipo=new stdClass();
                        $tipo->email= $usuario->email;
                        $tipo->tipo= $usuario->tipo;
                        //$tipo = new User2($usuario->id, $usuario->nombre,$usuario->dni, $usuario->obraSocial, $usuario->tipo);
                        echo json_encode( AutentificadorJWT::CrearToken($tipo));
                        $flag = true;
                        
                    }
                    
                }

                if( $flag == true){
                    echo Response::respuesta(1, "token generado");
                }
                else{
                    echo Response::respuesta(-1, "email y clave invalidos");
                }
            }
            else
            {
                echo Response::respuesta(0, "Agregue las key que faltan: email y clave");
            }
        }else{
            echo Response::respuesta(0, "Debe ser un POST");
        }
        break;
    case '/pizzas':

        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {

            $headers = apache_request_headers();

            //var_dump($headers['token']);
            if  (isset($headers['token']) == true ){
                
                AutentificadorJWT::VerificarToken($headers['token']);
                
                $tipoUsuario = (AutentificadorJWT::ObtenerData($headers['token'])->tipo);
                
                $emailUsuario = (AutentificadorJWT::ObtenerData($headers['token'])->email);
                //echo $tipoUsuario;

                if($tipoUsuario == "encargado"){
                    if  (isset($_POST['tipo']) == true && isset($_POST['precio']) == true&&
                    isset($_POST['stock']) == true && isset($_POST['sabor']) == true && isset($_FILES['foto']))
                    {
                        $tipo = $_POST['tipo'];
                        $precio = $_POST['precio'];
                        $stock = $_POST['stock'];
                        $sabor = $_POST['sabor'];

                        $tipoValido = false;
                        if($tipo == 'molde' || $tipo == 'piedra'  ){
                            $tipoValido = true;
                        }else{
                            echo Response::respuesta(-1, "tipo invalido molde o piedra");
                        }

                        $saboresValido = false;
                        
                        if($sabor == 'jamon' || $sabor == 'napo' || $sabor == 'muzza' ){
                            $saboresValido = true;
                        }
                        else{
                            echo Response::respuesta(-1, "sabor invalido jamon napo o muzza");
                        }

                        if($saboresValido && $tipoValido)
                        {
                            $arrayPizzas = Archivos::LeeJson("data/pizzas.json");

                            $flag =false;
                            foreach($arrayPizzas as $pizza){
                                $tipoP =$pizza->tipo;
                                $tipoS =$pizza->sabor;
                                if($tipoP == $tipo && $tipoS == $sabor ){
                                    $flag=true;
                                }
                            }
                            if($flag==false){

                                
                                Archivos::GuardarImagenConNombre($emailUsuario);

                                Archivos::addImageWatermark ('Imagenes/'.$emailUsuario.'.jpg', 'marcasDeAgua/corona.png', 'Imagenes/'.$emailUsuario.'.jpg', 30);

                                $pizza = new stdClass();
                                $pizza->tipo =$tipo;
                                $pizza->precio =$precio;
                                $pizza->stock =$stock;
                                $pizza->sabor =$sabor;
                                Archivos::GuardarJSON("data/pizzas.json", $pizza);
                                echo Response::respuesta(1, "Pizza Guardada");
                            }
                            else{
                                echo Response::respuesta(-1, "este tipo - sabor de pizza ya esta cargada");
                            }
                            
                        }

                    }
                    else
                    {
                        echo Response::respuesta(0, "Agregue las key que faltan: tipo, precio, stock, sabor y file foto");
                    }
                }
                else{
                    echo Response::respuesta(0, "No es encargado");
                }
            }
            else{
                echo Response::respuesta(0, "Cargue el token en el header");
            }
        }
        if($_SERVER['REQUEST_METHOD'] == 'GET')
        {

            $headers = apache_request_headers();

            //var_dump($headers['token']);
            if  (isset($headers['token']) == true ){
                
                AutentificadorJWT::VerificarToken($headers['token']);
                
                $tipoUsuario = (AutentificadorJWT::ObtenerData($headers['token'])->tipo);
                //echo $tipoUsuario;

                if($tipoUsuario == "encargado"){
                    $arrayPizzas = Archivos::LeeJson("data/pizzas.json");
                    echo json_encode($arrayPizzas);
                }
                else if($tipoUsuario == "cliente"){
                    $arrayPizzas = Archivos::LeeJson("data/pizzas.json");

                    $array = array();
                    foreach($arrayPizzas as $pizza){
                        $pizzaMuestra = new stdClass();
                        
                        $pizzaMuestra->tipo =$pizza->tipo;
                        $pizzaMuestra->precio =$pizza->precio;
                        $pizzaMuestra->sabor =$pizza->sabor;
                        array_push($array, $pizzaMuestra);
                        
                    }
                    echo json_encode($array);
                }
                else{
                    echo Response::respuesta(0, "No token valido");
                }
            }else{
                echo Response::respuesta(0, "Ingrese el token en el header");
            }
        }

        break;
    case '/ventas':

        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $headers = apache_request_headers();

            //var_dump($headers['token']);
            if  (isset($headers['token']) == true ){
                
                AutentificadorJWT::VerificarToken($headers['token']);
                
                $tipoUsuario = (AutentificadorJWT::ObtenerData($headers['token'])->tipo);
                $emailUsuario = (AutentificadorJWT::ObtenerData($headers['token'])->email);
                //echo $tipoUsuario;

                if($tipoUsuario == "cliente"){
                    if  (isset($_POST['tipo']) == true && isset($_POST['sabor']) == true)
                    {
                        $tipo = $_POST['tipo'];                       
                        $sabor = $_POST['sabor'];

                        $arrayPizzas = Archivos::LeeJson("data/pizzas.json");
                        $array = array();
                        foreach($arrayPizzas as $pizza){
                            
                            if($pizza->sabor == $sabor && $pizza->tipo == $tipo && $pizza->stock >=1){
                                $cant = $pizza->stock;
                                $pizza->stock = $cant - 1;
                                $monto = $pizza->precio;
                                array_push($array, $pizza);
                               

                                $venta = new stdClass();
                                $venta->email = $emailUsuario;
                                $venta->tipo = $tipoUsuario;
                                $venta->sabor = $pizza->sabor;
                                $venta->monto = $monto;
                                $venta->dia = date('d-m-Y');

                                Archivos::GuardarJSON("data/ventas.json", $venta);
                                echo Response::respuesta(1, "Pizza vendida debe pagar ".$monto.'$');
                            }
                            else{
                                array_push($array, $pizza);
                            }
                        }
                        Archivos::GuardarArray("data/pizzas.json",$array);
                    }
                    else
                    {
                        echo Response::respuesta(0, "Agregue las key que faltan: ");
                    }
                }else{
                    echo Response::respuesta(0, "Debe ser un POST");
                }
            }
        }

        if($_SERVER['REQUEST_METHOD'] == 'GET')
        {
            $headers = apache_request_headers();

            //var_dump($headers['token']);
            if  (isset($headers['token']) == true ){
                
                AutentificadorJWT::VerificarToken($headers['token']);
                
                $tipoUsuario = (AutentificadorJWT::ObtenerData($headers['token'])->tipo);
                $emailUsuario = (AutentificadorJWT::ObtenerData($headers['token'])->email);
                //echo $tipoUsuario;

                if($tipoUsuario == "cliente"){
                    $arrayVentas =  Archivos::LeeJson("data/ventas.json");
                    foreach($arrayVentas as $venta){
                        if($venta->email == $emailUsuario ){
                            var_dump($venta);
                        }
                        
                    }
                }
                if($tipoUsuario == "encargado"){
                    $arrayVentas =  Archivos::LeeJson("data/ventas.json");
                    $cantidad =0;
                    $total =0;
                    foreach($arrayVentas as $venta){
                        $precio = $venta->monto;
                        $total =$total + $precio;
                        $cantidad ++;
                        
                    }
                    echo Response::respuesta(1, "Cantidad de ventas ".$cantidad." Por un total de ".$total).'$';
                }

            }
        }


        break;

    // case '/ejemploGET':

    //     if($_SERVER['REQUEST_METHOD'] == 'GET')
    //     {
    //         if  (isset($_GET['nombre']) == true && isset($_GET['clave']) == true)
    //         {

    //         }
    //         else
    //         {
    //             echo Response::respuesta(0, "Agregue las key que faltan: ");
    //         }
    //     }else{
    //         echo Response::respuesta(0, "Debe ser un GET");
    //     }

    //     break;

    // case '/ejemploPOST':

    //     if($_SERVER['REQUEST_METHOD'] == 'POST')
    //     {
    //         if  (isset($_POST['nombre']) == true && isset($_POST['clave']) == true)
    //         {

    //         }
    //         else
    //         {
    //             echo Response::respuesta(0, "Agregue las key que faltan: ");
    //         }
    //     }else{
    //         echo Response::respuesta(0, "Debe ser un POST");
    //     }

    //     break;
    }