<?php
/*
 * class EDI
 */

class EDI {
    
    public $str_edi = '';
    public $segmentos = 0;
    
    function __construct($UNA = "UNA:+.? '\n") {
        $this->str_edi = $UNA;
        return;
    }
    
    function agregar_segmento($componente, $dato)
    {
        $this->str_edi .= $componente . "+" . $dato . "'\n";
        $this->segmentos++;
    }
    
    function EDIficar()
    {
        return $this->str_edi;
    }
}

// Funciones de soporte

// Codigo orden = PRIMARY en opsal_ordenes
// operacion: 34 = gate in, 36 = gate out, 999 = logistic move report
function crear_EDI($codigo_orden)
{  
    
    $c = 'SELECT `codigo_orden`, `codigo_contenedor`, `tipo_contenedor`, `ISO`, `codigo_agencia`, `codigo_posicion`, `nivel`, `clase`, `tara`, `chasis`, `chasis_egreso`, `transportista_ingreso`, `transportista_egreso`, `buque_ingreso`, `buque_egreso`, `cheque_ingreso`, `cheque_egreso`, `cepa_salida`, `arivu_ingreso`, `arivu_referencia`, `observaciones_egreso`, `observaciones_ingreso`, `destino`, `estado`, `fechatiempo_ingreso`, `fechatiempo_egreso`, `ingresado_por`, `egresado_por`, `sucio`, `tipo_salida`, `eir_ingreso`, `eir_egreso`, `ingreso_con_danos`, `cliente_ingreso`, `chofer_ingreso`, `chofer_egreso`, `booking_number`, `booking_number_ingreso` FROM `opsal_ordenes` LEFT JOIN `opsal_tipo_contenedores` USING (tipo_contenedor) WHERE `codigo_orden` = "'.$codigo_orden.'"';
    $r = db_consultar($c);
    
    if (mysqli_num_rows($r) == 0)
        return false;
    
    $DATOS = db_fetch($r);
    
    $c = 'SELECT `ID_movimiento` FROM `opsal_movimientos` WHERE `codigo_orden` = "'.$codigo_orden.'" ORDER BY ID_movimiento DESC LIMIT 1';
    $r = db_consultar($c);
    $MOVIMIENTO = db_fetch($r);
    
    $c = 'SELECT `codigo_usuario`, `activo`, `unb_ver`, `ung`,`sender_id`, `receiver_id`, `metodo`, `usuario`, `contrasena`, `host`, `dir_work`, `dir_out`, `dir_in`, `loc165` FROM `edi` WHERE codigo_usuario="'.$DATOS['codigo_agencia'].'"';
    $r = db_consultar($c);
    $EDI = db_fetch($r);
    
    $Control_Nbr = $MOVIMIENTO['ID_movimiento'];
    $SENDER_ID = $EDI['sender_id'];
    $RECEIVER_ID = $EDI['receiver_id'];
    $YYMMDD = date('ymd');
    $CCYYMMDDHHMM = ( $DATOS['estado'] == 'dentro' ? date('YmdHi',strtotime($DATOS['fechatiempo_ingreso'])) : date('YmdHi',strtotime($DATOS['fechatiempo_egreso'])) );
    $HHMM = date('Hi');
    $BOOKIN_NUMBER = ( $DATOS['estado'] == 'dentro' ? $DATOS['booking_number_ingreso'] : $DATOS['booking_number'] );
    $operacion = ($DATOS['estado'] == 'dentro' ? '34' : '36');
    $IO = ($DATOS['estado'] == 'dentro' ? '3' : '2'); // 3 Import, 2 Export
    
    $edi = new EDI(null);
    $edi->agregar_segmento('UNB','UNOA:'.$EDI['unb_ver'].'+'.$SENDER_ID.'+'.$RECEIVER_ID.'+'.$YYMMDD.':'.$HHMM.'+'.$Control_Nbr);
    
    if ($EDI['ung'])
        $edi->agregar_segmento('UNG','CODECO+'.$SENDER_ID.'+'.$RECEIVER_ID.'+'.$YYMMDD.':'.$HHMM.'+'.$Control_Nbr.'+UN+D:95B');
    
    $edi->agregar_segmento('UNH',$Control_Nbr.'+CODECO:D:95B:UN+ITG14');

    // BGM  Beginning Of Message    (Seg. Seq Nbr 001)
    // OPERACION 34 = gate in, 36 = gate out, 999 = logistic move report
    $edi->agregar_segmento('BGM',$operacion.'+'.$Control_Nbr.'+9');

    // TDT  Details Of Transport    (Segment Sequence Nbr 004)
    $edi->agregar_segmento('TDT','1+MERCHANT+3');

    // NAD  Name And Address    (Seg. Seq Nbr 008)
    //$edi->agregar_segmento('NAD','');

    // EQD  Equipment Details    (Seg. Seq Nbr 020)
    $edi->agregar_segmento('EQD','CN+'.$DATOS['codigo_contenedor'].'+'.$DATOS['ISO'].':102:5++'.$IO.'+4');

    // RFF  Reference    (Seg. Seq Nbr 021)
    
    if ($BOOKIN_NUMBER)
        $edi->agregar_segmento('RFF','BN:'.$BOOKIN_NUMBER);
    
    // DTM  Date/Time/Period    (Seg. Seq Nbr 023)
    $edi->agregar_segmento('DTM','7:'.$CCYYMMDDHHMM.':203');

    // LOC  Place/Location Identification    (Seg. Seq Nbr 024)
    $edi->agregar_segmento('LOC','165+'.$EDI['loc165'].':139:6+'.$SENDER_ID);
    
    if ($IO == '2') // export
        $edi->agregar_segmento('LOC','99+'.$EDI['loc165']);
    
    // MEA  Measurements    (Seg. Seq Nbr 025)
    $edi->agregar_segmento('MEA','WT+G+KGM:'.$DATOS['tara']);

    // SEL  Seal Number   (Seg. Seq Nbr 028)
    
    if ($IO == 3) // import
        $SEAL = $DATOS['ingreso_marchamo'];
    else
        $SEAL = $DATOS['egreso_marchamo'];
    
    if ($SEAL === '')
        $SEAL = 'NS+NS';
    
    if ($SEAL === '-')
        $SEAL = 'NLSB+SH';
    
    $edi->agregar_segmento('SEL',$SEAL);

    // FTX
    if ($IO == 3) // import
        $edi->agregar_segmento('FTX', 'AAI+++'.$DATOS['cliente_ingreso'].'/'.$DATOS['transportista_ingreso'].'/'.$DATOS['arivu_referencia']);
    else
        $edi->agregar_segmento('FTX', 'AAI+++'.$DATOS['cliente_egreso'].'/'.$DATOS['transportista_egreso'].'/'.$DATOS['arivu_referencia']);
    
    // CNT  Control Totals (Seg Seq Nbr 036)
    $edi->agregar_segmento('CNT','16:1');

    // Message trailer UNT-Segment
    $edi->agregar_segmento('UNT',$edi->segmentos.'+'.$Control_Nbr);
    
    // Interchange trailer UNZ-Segment
    $edi->agregar_segmento('UNZ','1+'.$Control_Nbr);
    
    return $edi->EDIficar();
}

function enviar_edi($codigo_orden, $prueba=false)
{    
    
    $c = 'SELECT `codigo_agencia`, `codigo_contenedor` FROM `opsal_ordenes` WHERE `codigo_orden` = "'.$codigo_orden.'"';
    $r = db_consultar($c);    
    $DATOS = db_fetch($r);
    
    $c = 'SELECT `codigo_usuario`, `activo`, `unb_ver`, `sender_id`, `receiver_id`, `metodo`, `usuario`, `contrasena`, `host`, `dir_work`, `dir_out`, `dir_in`, `loc165`, `metodo` FROM `edi` WHERE codigo_usuario="'.$DATOS['codigo_agencia'].'"';
    $r = db_consultar($c);
    $EDI = db_fetch($r);
    
    if ($EDI['activo'] == '0')
    {
        return;
    }
    
    $DATOS_EDI = crear_EDI($codigo_orden); 
    
    
    if ($EDI['metodo'] == 'ftp')
    {
        // establecer una conexión básica
        $conn_id = ftp_connect($EDI['host']); 
        
        ftp_pasv($conn_id, true);

        // iniciar una sesión con nombre de usuario y contraseña
        $login_result = ftp_login($conn_id, $EDI['usuario'], $EDI['contrasena']); 

        // verificar la conexión
        if ((!$conn_id) || (!$login_result)) {  
            return false;
        }

        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $DATOS_EDI);
        
        
        
        $nombre_archivo = date('Ymd_His_').uniqid('', true).'_'.$DATOS['codigo_contenedor'].'.edi';
        
        if ( $prueba === false )
        {
            // Subir el original
            rewind($fp);
            ftp_chdir($conn_id, $EDI['dir_out']); 
            $upload = ftp_fput($conn_id, $nombre_archivo, $fp, FTP_ASCII);  
        }
        
        // Subir la copia/prueba
       
        rewind($fp);
        ftp_chdir($conn_id, $EDI['dir_work']); 
        $resultado_prueba = ftp_fput($conn_id, $nombre_archivo, $fp, FTP_ASCII);
        

        // cerrar la conexión ftp 
        ftp_close($conn_id);
       
        // Prueba
        
        if ($prueba)
        {
            return $resultado_prueba;
        }
        
        // comprobar el estado de la subida
        return ! $upload;
        

    }
    
    
    
    
}
?>