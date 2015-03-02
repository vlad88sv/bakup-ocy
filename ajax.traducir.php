<?php

define('_columna_',1);
define('_fila_',2);

$_POST['traducir'] = trim(strtoupper($_POST['traducir']));

if (empty($_POST['traducir']) || preg_match('/([A-Z]{1,2})([0-9]{1,2})/',$_POST['traducir'],$codigo_antiguo) != 1)
{
    echo '¿?';
    return;
}

//print_r($codigo_antiguo);



if ($codigo_antiguo[_fila_] < 1 || $codigo_antiguo[_fila_] > 47)
{
    echo $codigo_antiguo[_fila_].'?';
    return;
}

if ($codigo_antiguo[_fila_] % 2 == 0)
{   
    $codigo_antiguo[_fila_] = ($codigo_antiguo[_fila_] - 1);
}

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

echo $tcol[$codigo_antiguo[_columna_]].($codigo_antiguo[_fila_]+8);
?>
