<?php

$c = 'SELECT numero_fiscal, cantidad, cu, t3.`nombre_fiscal`, t3.`tipo_de_documento`, t3.`registro_de_iva`, t3.`nit`, t3.`direccion`, t3.`giro`, t3.departamento, `servicio`, `codigo_factura`, t2.`usuario` AS "operador" , `codigo_agencia`, t3.`usuario` AS "agencia", `fecha_creada`, total_sin_iva, iva, ((total_sin_iva/100)*iva) AS solo_iva, total FROM `facturas` AS t1 LEFT JOIN `opsal_usuarios` AS t2 USING(codigo_usuario) LEFT JOIN `opsal_usuarios` AS t3 ON t1.codigo_agencia = t3.codigo_usuario WHERE t1.`uniqid` = "'.db_codex($_GET['uniqid']).'"';
$r = db_consultar($c);

if (!$r)
{
    echo 'Sucedió un error en la consulta SQL. STOP.';
    return;
}


while ($fila = mysqli_fetch_assoc($r))
{
    $f[] = $fila;
}

include 'PHP/PHPExcel/PHPExcel.php';
$inputFileName = 'IMP/C.xls';
$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

// Fecha
$objPHPExcel->getActiveSheet()->setCellValue('C6', '                '.date('d/m/Y') );
$objPHPExcel->getActiveSheet()->setCellValue('C7', '                  '.strtoupper($f[0]['nombre_fiscal']) );
$objPHPExcel->getActiveSheet()->setCellValue('C8', '                       '.strtoupper($f[0]['direccion']) );
$objPHPExcel->getActiveSheet()->setCellValue('D9', '       '.strtoupper($f[0]['departamento']) );

$objPHPExcel->getActiveSheet()->setCellValue('J6', '     '.strtoupper($f[0]['nit']) );
$objPHPExcel->getActiveSheet()->setCellValue('J7', '        '.  strtoupper($f[0]['registro_de_iva']));
$objPHPExcel->getActiveSheet()->setCellValue('J8', '       '.substr(strtoupper($f[0]['giro']),0 , 30) );

$objPHPExcel->getActiveSheet()->setCellValue('L3', $f[0]['numero_fiscal'] );


$subtotal = 0;
$iva = 0;
$total = 0;    

$linea_base = 14;

// Detalle
foreach ($f as $bdetalle)
{
    $total += $bdetalle['total'];
    $subtotal += $bdetalle['total_sin_iva'];
    $iva += $bdetalle['iva'];

    // Conceptos
        // -Cantidad
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$linea_base, $bdetalle['cantidad']);
        // -Concepto
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$linea_base, $bdetalle['servicio']);
        // -Unitario
        $objPHPExcel->getActiveSheet()->setCellValue('I'.$linea_base, $bdetalle['cu']);
        // -Asaber
        $objPHPExcel->getActiveSheet()->setCellValue('M'.$linea_base, "$0.00");        
        // -Total
        $objPHPExcel->getActiveSheet()->setCellValue('N'.$linea_base, $bdetalle['total_sin_iva']);    
        
        $linea_base += 3;
}

// Numero en letras
$objPHPExcel->getActiveSheet()->setCellValue('C48', '         '.numero_a_letras(numero2($total)));
// Total sin IVA
$objPHPExcel->getActiveSheet()->setCellValue('N48', dinero($subtotal));
// IVA
$objPHPExcel->getActiveSheet()->setCellValue('N50', dinero($iva));
// Total
$objPHPExcel->getActiveSheet()->setCellValue('N51', dinero($total));
$objPHPExcel->getActiveSheet()->setCellValue('N52', dinero(0));
$objPHPExcel->getActiveSheet()->setCellValue('N53', dinero(0));
$objPHPExcel->getActiveSheet()->setCellValue('N54', dinero($total));


$objWriter->save("IMP/cf.xls");
$objPHPExcel->disconnectWorksheets();
unset($objPHPExcel);
?>
<p>
    Si su navegador no le ofrece la descarga automaticamente, favor haga clic en este enlace: <a href="/IMP/cf.xls">cf.xls</a>.
</p>
<script>
    window.location = '/IMP/cf.xls';
</script>