<?php
// conversor *magico* para tablas antiguas de opsal

require_once('../config.php');
require_once (__PHPDIR__."vital.php");

$c = 'SELECT codigo_usuario, usuario FROM opsal_usuarios WHERE nivel="agencia" ORDER BY usuario ASC';
$r = db_consultar($c);

$options_agencia = '<option selected="selected" value="">Seleccione una</option>';
if (mysqli_num_rows($r) > 0)
{
    while ($registro = mysqli_fetch_assoc($r))
    {
        $options_agencia .= '<option value="'.$registro['codigo_usuario'].'">'.$registro['usuario'].'</option>';
    }
}
/*
$c = 'SELECT x2 FROM opsal_posicion GROUP BY x2 ORDER BY x ASC';
$r = db_consultar($c);

while ($f = mysqli_fetch_assoc($r))
{
    echo '$tcol[""] = "'.$f['x2'].'";<br />';
}

return;
*/

// Transcodificacion de mapa - col
$tcol = array();
/* no existian antes
$tcol[""] = "A";
$tcol[""] = "B";
$tcol[""] = "C";
$tcol[""] = "D";
$tcol[""] = "E";
$tcol[""] = "F";
*/
$tcol["A"]  = "G";
$tcol["B"]  = "H";
$tcol["C"]  = "I";
$tcol["D"]  = "J";
$tcol["E"]  = "K";
$tcol["F"]  = "L";
$tcol["G"]  = "M";
$tcol["H"]  = "N";
$tcol["I"]  = "O";
$tcol["J"]  = "P";
$tcol["K"]  = "Q";
$tcol["L"]  = "R";
$tcol["M"]  = "S";
$tcol["N"]  = "T";
$tcol["O"]  = "U";
$tcol["P"]  = "V";
$tcol["Q"]  = "W";
$tcol["R"]  = "X";
$tcol["S"]  = "Y";
$tcol["T"]  = "Z";
$tcol["U"]  = "AA";
$tcol["V"]  = "AB";
$tcol["W"]  = "AC";
$tcol["X"]  = "AD";
$tcol["Y"]  = "AE";
$tcol["Z"]  = "AF";
$tcol["AA"] = "AG";
$tcol["AB"] = "AH";
$tcol["AC"] = "AI";
$tcol["AD"] = "AJ";
$tcol["AE"] = "AK";
$tcol["AF"] = "AL";
$tcol["AG"] = "AM";
$tcol["AH"] = "AN";
$tcol["AI"] = "AO";
$tcol["AJ"] = "AP";
$tcol["AK"] = "AQ";
$tcol["AL"] = "AR";
$tcol["AM"] = "AS";
$tcol["AN"] = "AT";
$tcol["AO"] = "AU";
$tcol["AP"] = "AV";
$tcol["AQ"] = "AW";
$tcol["AR"] = "AX";
$tcol["AS"] = "AY";
$tcol["AT"] = "BZ";
$tcol["AU"] = "BA";
$tcol["AV"] = "BB";
$tcol["CF"] = "BC";
$tcol["CE"] = "BD";
$tcol["CD"] = "BE";
$tcol["CC"] = "BF";
$tcol["CB"] = "BG";
$tcol["CA"] = "BH";

/*
$i = 0;
foreach ($tcol as $index => $col)
{
    echo "<p>$i, $index, $col</p>";
    $i++;
}

return;
*/

// Offset fila
$offset_fila = 8;


if (isset($_FILES['archivo']))
{
    $codigo_agencia = $_POST['codigo_agencia'];
    
    if (($gestor = fopen($_FILES['archivo']['tmp_name'], "r")) !== FALSE) {
        while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
          
            // Serial del contenedor, solo eliminamos el guión
            $contenedor = str_replace('-','',$datos[0]);
            
            // Tipo - ordenar tipo, luego tamaño. Mayusculas
            $tipo = strtoupper(preg_replace('/(\d{2})(\w{2})/','$2$1',$datos[1]));
            
            // Nombre del transportista y buscamos una fecha de arivu
            // La fecha de arivu puede o no existir
            // La fecha de arivu puede estar en formato dd/mm/yy o dd-mm-yy
            // Eliminar datos adicionales variables (objetivo: tipo de contenedor)
            $transportia_fecha_arivu =  explode('+',preg_replace(array('/[\(|\)]/', '/\d{2}\w{2}/','/(\d{2})-(\d{2})-(\d{2})/','/(\d{2})\/(\d{2})\/(\d{2})/','/\s/'), array('','','$1/$2/$3','+20$3-$2-$1',''),$datos[2]));
            
            $transportista = preg_replace(array('/[\<\>]/', '/\s\w{2}\d{2}/', '/\w{2}\d{2}\s/'),'',$transportia_fecha_arivu[0]);
            $fecha_arivu = $transportia_fecha_arivu[1];
            
            // Traduccion dd/mm/yyyy a yyyy-mm-dd
            $fecha_ingreso = preg_replace('/(\d{2}).(\d{2}).(\d{4})/', '$3-$2-$1', $datos[3]);
            
            // Posicion [formato col,fila,nivel]
            $pos = explode('-',$datos[4]);
            
            // POS 6 es 1 si ya es la posicion nueva, 0 si es la antigua
            if ($datos[6] == 0)
            {
                // Transcodificacion entre sistema antiguo y nuevo
                $columna = $tcol[$pos[0]];
                $fila = (($pos[1] % 2 == 0 ? ($pos[1]+1) : $pos[1])+$offset_fila);
                $nivel = $pos[2];
            } else {
                $columna = $pos[0];
                $fila = ($pos[1] % 2 == 0 ? ($pos[1]+1) : $pos[1]);
                $nivel = $pos[2];
            }
            
            // Separar la tara del numero de arivu
            // Los primeros 4 numeros serán la tara
            // El siguiente juego de numeros (entre 5 y 7) son el numero de arivu
            $tara_arivu = explode('+',preg_replace('/.*?(\d{4})[^\d]*(\d*).*/','$1+$2',$datos[5]));
            $tara = $tara_arivu[0];
            $arivu = $tara_arivu[1];
            
            $clase = strtoupper(preg_replace('/.*[\<\(]+(\w{1})[\>\)]+.*/','$1',$datos[5]));
            if (strlen($clase) > 1)
                $clase = 'A';
            
            echo '<p>';
            
            $c = "INSERT INTO `opsal_ordenes` (observaciones_ingreso, ingresado_por, clase, estado, codigo_agencia, codigo_contenedor, tipo_contenedor, transportista_ingreso, arivu_ingreso, fechatiempo_ingreso, codigo_posicion, nivel, tara, arivu_referencia) VALUES('Datos migrados del sistema antiguo', 1, '$clase', 'dentro', '$codigo_agencia', '$contenedor','$tipo','$transportista','$fecha_arivu','$fecha_ingreso',(SELECT codigo_posicion FROM opsal_posicion WHERE x2='$columna' AND y2='$fila'),'$nivel', '$tara', '$arivu');";
            
            db_consultar ($c);
            $codigo_orden = mysqli_insert_id($db_link);    
            echo $c.'<br />';
            
            // Añadimos la estiba
            $c = "INSERT INTO `opsal_movimientos` (codigo_orden, codigo_posicion, nivel, codigo_usuario, cobrar_a, motivo, fechatiempo) VALUES ($codigo_orden, (SELECT codigo_posicion FROM opsal_posicion WHERE x2='$columna' AND y2='$fila'), $nivel, 1, $codigo_agencia, 'estiba','$fecha_ingreso')";
            
            db_consultar($c);
            echo $c;
            
            echo '</p>';
        }
        fclose($gestor);
    }
    return;
}
?>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="archivo" />
    <select name="codigo_agencia"><?php echo $options_agencia; ?></select>
    <input type="submit" value="PreProcesar" />
</form>