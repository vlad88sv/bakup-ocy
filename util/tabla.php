<?php
// Transcodificacion de mapa - col
$abc = strtoupper('abcdefghijklmnopqrstuvwxyz');
$offset_col = 6;
$tcol = array();

for ($x=0; $x < 100; $x++)
{
    switch ($x)
    {
        case $x > 47:
            $x1 = 'C'.$abc[53-$x];
            break;
        
        case $x > 25:
            $x1 = $abc[floor((($x-26)/26))].$abc[($x%26)];
            break;
        
        default:
            $x1 = $abc[($x%26)];
            break;       
        
    }
    $x2 = $abc[floor((($x+$offset_col)/26))].$abc[(($x+$offset_col)%26)];
    $tcol[$x1] = $x2;
}

$i = 0;
foreach ($tcol as $index => $col)
{
    echo "<p>$index, $col</p>";
    $i++;
}


// Offset fila
$offset_fila = 4;
?>