<?php

namespace App\Service\Processor\Tool; 

/*
 * Descripción: Es la clase aparece como conjunto de utilidades apara los procesos por estar en todos ellos y
 *              evitar su repetición  o por sacar el código del proceso y dejarlo  mas limpio y extendible.
 *              Hay funciones comunes y funciones solo para un proceso en especifico.
 */              
class ProcessorTool
{
     /***
     * Descripcion: Limpia una cadena de caracteres especiales acentos y espacios
     *              la funcion se utiliza para hacer el identificador de la denominacion
     *              
     *              
     * Parametros:
     *             string:  cadena a convertir       
     */
    public static function clean($string) {
        //remplazamos espacios en blanco y ponemos a minusculas
        $cadena = str_replace(' ', '-', strtolower($string));
        //Reemplazamos la A y a
        $cadena = str_replace(array('á', 'à', 'ä', 'â', 'ª'), array('a', 'a', 'a', 'a', 'a'),$cadena );
        //Reemplazamos la E y e
        $cadena = str_replace(array('é', 'è', 'ë', 'ê'), array('e', 'e', 'e', 'e'),$cadena );
        //Reemplazamos la I y i
        $cadena = str_replace( array('í', 'ì', 'ï', 'î'),array('i', 'i', 'i', 'i'),$cadena );
        //Reemplazamos la O y o
        $cadena = str_replace(array('ó', 'ò', 'ö', 'ô'),array('o', 'o', 'o', 'o'), $cadena );
        //Reemplazamos la U y u
        $cadena = str_replace(array('ú', 'ù', 'ü', 'û'),array('u', 'u', 'u', 'u'),$cadena );
        //Reemplazamos la N, n, C y c
        $cadena = str_replace(array('ñ', 'ç'),array('n', 'c'), $cadena);
        //quitamos carareres especiales
        $cadena = preg_replace('/[^A-Za-z0-9\-]/', '', $cadena);   
        
        return $cadena;
     }

}
