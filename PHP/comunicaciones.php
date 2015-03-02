<?php

/* PUSH FTP
 * $ops:
 * [datos] // string con los datos a cargar
 * [destino] // URI de destino
 * [servidor][host]
 * [servidor][usuario]
 * [servidor][clave]
 */
function com_ftp_push($ops)
{
    $temp = tmpfile();
    fwrite($temp, $ops['datos']);
    fseek($temp, 0);
    
    // set up basic connection
    $conn_id = ftp_connect($ops['servidor']['host']);
    
    // login with username and password
    $login_result = ftp_login($conn_id, $ops['servidor']['usuario'], $ops['servidor']['clave']);
    
    // upload a file
    if ( ! ftp_fput($conn_id, $ops['destino'], $temp, FTP_ASCII)) {
        
        guardar_error("Problema al cargar al host".$ops['servidor']['host'],'FTP','critico');
    }
    
    // close the connection
    ftp_close($conn_id);
    fclose($temp);
}
?>
