<?php
// Este archivo se encargará de levantar la sesión y la conexión a la base de datos.
setlocale                   (LC_ALL, 'es_SV.UTF-8');
date_default_timezone_set   ('America/El_Salvador');

$base = dirname(__FILE__);
require_once ("$base/bench.php"); // Generación de HTML: Comboboxes, etc.
require_once ("$base/ui.php"); // Generación de HTML: Comboboxes, etc.
require_once ("$base/stubs.php"); // Funciones varias
require_once ("$base/const.php"); // Constantes
require_once ("$base/sesion.php"); // Sesión
require_once ("$base/db.php"); // Conexión hacia la base de datos [depende de secreto.php]
require_once ("$base/db-stubs.php"); // Generación de objetos UI desde la base de datos [depende de ui.php]
require_once ("$base/db-ui.php"); // Generación de objetos UI desde la base de datos [depende de ui.php]
require_once ("$base/usuario.php"); // Gestión de datos de usuario [depende de sesion.php]
require_once ("$base/edi.php"); // EDIFACT
require_once ("$base/comunicaciones.php"); // PUSH para FTP y SFTP
require_once ("$base/phpMyEdit/phpMyEdit.class.php");

function parse_backtrace(){

    $raw = debug_backtrace();
    $output="";

    foreach($raw as $entry)
    {
            $output.="\nFile: ".$entry['file']." (Line: ".$entry['line'].")\n";
            $output.="Function: ".$entry['function']."\n";
            $output.="Args: ".implode(", ", $entry['args'])."\n";
    }

    return $output;
}


function DEPURAR($s,$f=0){if($f||isset($_GET['depurar'])){echo '<pre>'.$s.'</pre><br /><pre>'.parse_backtrace().'</pre><br />';}}

function Mensaje ($texto, $tipo=_M_INFO){
    switch ( $tipo ) {
        case _M_INFO:
        $id = "info";
        break;
        case _M_ERROR:
        $id = "error";
        break;
        case _M_NOTA:
        $id = "nota";
        break;
        default:
        return 'Error: no se definió el $tipo de mensaje';
    }

    return "<div id=\"$id\">".$texto."</div>";

}

?>
