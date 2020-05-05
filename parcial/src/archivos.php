<?php


class Archivos{

    //METODOS PARA EL MANEJO DE ARCHIVOS

    public static function GuardarJSON($archivo, $objeto)
    {
        // LEEMOS
        $file = fopen($archivo, 'r');
        $arrayString = fread($file, filesize($archivo));
        $arrayJSON = json_decode($arrayString);
        fclose($file);
        array_push($arrayJSON, $objeto);
        
        // ESCRIBIMOS
        $file = fopen($archivo, 'w');
        $rta = fwrite($file, json_encode($arrayJSON));
        fclose($file);

        return $rta;
    }

    public static function GuardarArray($archivo, $objeto)
    {
        // ESCRIBIMOS
        $file = fopen($archivo, 'w');
        $rta = fwrite($file, json_encode($objeto));
        fclose($file);

        return $rta;
    }

    

    public static function LeeJson($archivo){
        // LEEMOS
        $file = fopen($archivo, 'r');
        $arrayString = fread($file, filesize($archivo));
        $arrayJSON = json_decode($arrayString);

        fclose($file);
        
        return  $arrayJSON;
    }



//METODOS PARA EL GUARDADO DE IMAGENES

    public static function GuardarImagen(){
        $tmp_name = $_FILES['foto']['tmp_name'];
        $name= $_FILES['foto']['name'];
    
        $nombre = explode('.',$name)[0].'--'.time().'--.'.explode('.',$name)[1];
    
        $carpeta = 'Imagenes/'.$nombre;
        move_uploaded_file($tmp_name, $carpeta );
        //ese move devuelve 1 si quiero saber si se guarod la imagen
    }

    public static function GuardarImagenConNombre($nombre){
        $tmp_name = $_FILES['foto']['tmp_name'];
        $name= $_FILES['foto']['name'];

        $carpeta = 'Imagenes/'.$nombre.'.'.explode('.',$name)[1];
        move_uploaded_file($tmp_name, $carpeta );
        //ese move devuelve 1 si quiero saber si se guarod la imagen
    }
    
    public static function BorrarImagen($OrigenCarpEImgABorrar, $DestinoCarpEImgABorrar){
        if(copy($OrigenCarpEImgABorrar, $DestinoCarpEImgABorrar)){
            unlink($OrigenCarpEImgABorrar);
            return 1;
        }
        else{
            return 0;
        }
    }

    




    //METODOS PARA MARCA DE AGUAS

    public static function AddTextWatermark($src, $watermark, $save=NULL) { 
        list($width, $height) = getimagesize($src);
        $image_color = imagecreatetruecolor($width, $height);
        $image = imagecreatefromjpeg($src);
        imagecopyresampled($image_color, $image, 0, 0, 0, 0, $width, $height, $width, $height); //corro la imagen
        $txtcolor = imagecolorallocate($image_color, 255, 255, 255); //modifico el color
        $font = dirname(__FILE__).'../Roboto-Black.ttf';
        $font_size = 50; //tamaño de la letra
        imagettftext($image_color, $font_size, 0, 50, 150, $txtcolor, $font, $watermark); //modifico el lugar donde esta la marca
        if ($save<>'') {
           imagejpeg ($image_color, $save, 100); 
           
        } else {
            header('Content-Type: image/jpeg');
            imagejpeg($image_color, null, 100);
            
        }
        imagedestroy($image); 
        imagedestroy($image_color); 
    }
    
    
       // Función para agregar marca de agua de imagen sobre imágenes
       public static function AddImageWatermark($SourceFile, $WaterMark, $DestinationFile=NULL, $opacity) {
        $main_img = $SourceFile; 
        $watermark_img = $WaterMark; 
        $padding = 5; 
        $opacity = $opacity;
        // crear marca de agua
        $watermark = imagecreatefrompng($watermark_img); 
        $image = imagecreatefromjpeg($main_img); 
        if(!$image || !$watermark) die("Error: La imagen principal o la imagen de marca de agua no se pudo cargar!");
        $watermark_size = getimagesize($watermark_img);
        $watermark_width = $watermark_size[0]; 
        $watermark_height = $watermark_size[1]; 
        $image_size = getimagesize($main_img); 
        $dest_x = $image_size[0] - $watermark_width - $padding; 
        $dest_y = $image_size[1] - $watermark_height - $padding;
        imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $opacity);
        if ($DestinationFile<>'') {
           imagejpeg($image, $DestinationFile, 100); 
        } else {
            header('Content-Type: image/jpeg');
            imagejpeg($image);
        }
        imagedestroy($image); 
        imagedestroy($watermark); 
       }




       //METODOS PARA SERIALIZADO

       function GuardarDataSerializada($archivo,$objeto ) { 
        $serializo = serialize($objeto);
        Archivos::guardarJSON($archivo,$serializo);
    }
    
    function LeerDataSerializada($archivo ) { 
        $arrayConDatos = Archivos::LeeJson($archivo);
        foreach($arrayConDatos as $dato){
            $deserializo=  unserialize($dato);
            var_dump($deserializo);
        }
    }


}



?>