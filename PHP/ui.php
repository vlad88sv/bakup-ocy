<?php
function ui_destruir_vacios($cadena)
{
return preg_replace("/(\s)?\w+=\"\"/","",$cadena);
}
function ui_img ($id_gui, $src,$alt="[Imagen no puso ser cargada]"){
	return ui_destruir_vacios('<img id="'.$id_gui.'" alt="'.$alt.'" src="'.$src.'" />');
}
function ui_href ($id_gui, $href, $texto, $clase="", $extra=""){
	return ui_destruir_vacios('<a id="'.$id_gui.'" href="'.$href.'" class="' . $clase . '" ' . $extra . '>'.$texto.'</a>');
}
function ui_A ($id_gui, $texto, $clase="", $extra=""){
	return '<a id="'.$id_gui.'" class="' . $clase . '" ' . $extra . '>'.$texto.'</a>';
}
function ui_combobox ($id_gui, $opciones, $selected = "", $clase="", $estilo="") {
	$opciones = str_replace('value="'.$selected.'"', 'selected="selected" value="'.$selected.'"', $opciones);
	return '<select id="' . $id_gui . '" name="' . $id_gui . '" style="' . $estilo . '">'. $opciones . '</select>';
}
function ui_input ($id_gui, $valor="", $tipo="text", $clase="", $estilo="", $extra ="") {
	$tipo = empty($tipo) ? "text" : $tipo;
	return '<input type="'.$tipo.'" id="' . $id_gui . '" name="' . $id_gui . '" class="' . $clase . '" style="' . $estilo . '" value="' . $valor .'" '.$extra.'></input>';
}
function ui_textarea ($id_gui, $valor="", $clase="", $estilo="", $extra="") {
	return "<textarea id='$id_gui' name='$id_gui' class='$clase' style='$estilo' $extra>$valor</textarea>";
}
function ui_th ($valor, $clase="") {
	return "<th class='$clase'>$valor</td>";
}
function ui_td ($valor, $clase="", $estilo="") {
	return "<td class='$clase' style='$estilo'>$valor</td>";
}
function ui_tr ($valor) {
	return "<tr>$valor</tr>";
}
function ui_optionbox_nosi ($id_gui, $valorNo = 0, $valorSi = 1, $TextoSi = "Si", $TextoNo = "No") {
	return "<input id='$id_gui' name='$id_gui' type='radio' checked='checked' value='$valorNo'>$TextoNo</input>" . '&nbsp;&nbsp;&nbsp;&nbsp;'."<input id='$id_gui' name='$id_gui' type='radio' value='$valorSi'>$TextoSi</input>";
}
function ui_combobox_o_meses (){
	$opciones = '';
	for ($i = 1; $i < 13; $i++) {
		$opciones .= '<option value="'.$i.'">['.$i.'] '.strftime('%B', mktime (0,0,0,$i,1,2009)).'</option>';
	}
	return $opciones;
}
function ui_combobox_o_anios (){
	$opciones = '';
	for ($i = 0; $i < 13; $i++) {
		$opciones .= '<option value="'.(date('Y') - $i).'">'.(date('Y') - $i).'</option>';
	}
	return $opciones;
}

function ui_combobox_o_anios_futuro (){
	$opciones = '';
	for ($i = 0; $i < 11; $i++) {
		$opciones .= '<option value="'.(date('Y') + $i).'">'.(date('Y') + $i).'</option>';
	}
	return $opciones;
}

function ui_js_ini_datepicker ($inicio = '', $fin = '', $extra = ''){
	if ($inicio) $inicio = ", minDate: '$inicio'";
	if ($fin) $fin = ", maxDate: '$fin'";
	return "$('.date-pick').datepicker({dateFormat: 'dd-mm-yy' $inicio $fin $extra});";
}
function ui_js_ini_slider ($id_gui, $objetivo = '', $value = '0', $inicio = '0', $fin = '100', $paso = '1'){

	return "$('#slider').slider({value:100, min: 0, max: 500, step: 50, slide: function(event, ui) { $('#amount').val('$' + ui.value); }	});
		$('#amount').val( $('#slider').slider('value'));
		});
		";
}

function ui_array_a_opciones($array,$swap = false)
{
	$buffer = '';
	foreach ($array as $valor => $texto)
	{
		if ($swap)
			$buffer .= '<option value="'.$texto.'">'.$valor.'</option>'."\n";
		else
			$buffer .= '<option value="'.$valor.'">'.$texto.'</option>'."\n";
	}

	return $buffer;
}
function GENERAR_SOCIAL($t="")
{
	global $HEAD_titulo;
	$b = '<div style="text-align:right">';
	$b .=
	$t .
	// FaceBook
	ui_href('',sprintf('http://www.facebook.com/sharer.php?u=%s&t=%s&src=sp',urlencode(PROY_URL_ACTUAL_DINAMICA), urlencode($HEAD_titulo)),'<img class="social" src="IMG/social/facebook.gif" title="FaceBook" alt="FaceBook" />','','target="_blank"')
	.
	// del.icio.us
	ui_href('',sprintf('http://del.icio.us/post?url=%s&title=%s',urlencode(PROY_URL), urlencode($HEAD_titulo)),'<img class="social" src="IMG/social/delicious.gif" title="del.icio.us" alt="del.icio.us" />','','target="_blank"')
	.
	// Digg
	ui_href('',sprintf('http://digg.com/submit?phase=2&url=%s&title=%s',urlencode(PROY_URL), urlencode(utf8_decode($HEAD_titulo))),'<img class="social" src="IMG/social/digg.gif" title="Digg" alt="Digg" />','','target="_blank"')
	.
	// StumbleUpon
	ui_href('',sprintf('http://www.stumbleupon.com/submit?url=%s&title=%s',urlencode(PROY_URL), urlencode($HEAD_titulo)),'<img class="social" src="IMG/social/stumbleupon.gif" title="StumbleUpon" alt="StumbleUpon" />','','target="_blank"')
	.
	// Twitter
	ui_href('',sprintf('http://twitter.com/home?status=Actualmente viendo %s, %s',urlencode(PROY_URL_ACTUAL_DINAMICA), urlencode($HEAD_titulo)),'<img class="social" src="IMG/social/twitter.gif" title="Twitter" alt="Twitter" />','','target="_blank"')
	;
	$b .= '</div>';
	return $b;
}

function Rejilla_Resultados($r,$horizontal = false)
{
    if (isset($_GET['fb']))
	$MaxPorFila = 3;
    else
	$MaxPorFila = 4;
    
    $nElementos = mysqli_num_rows($r);
    $nFilas = ceil($nElementos / $MaxPorFila);

    $bELEMENTOS = '<table style="width:100%;table-layout:fixed;border-collapse:collapse;margin:0;border:none;padding:0">';
    for($i=0;$i<$nFilas;$i++)
    {
        $bELEMENTOS .= '<tr>';
        for($j=0;$j<$MaxPorFila;$j++)
        {
	    $bELEMENTOS .= '<td style="text-align:center;vertical-align:top;">';
            if($f = mysqli_fetch_assoc($r))
            {
                if (empty($f['variedad_foto']))
                {
                    $f['variedad_foto'] = 'IMG/stock/sin_imagen_157_234.jpg';
                }
                else
                {
		    if ($horizontal)
		    {
			$lazy_img = imagen_URL('cargando_imagen_horizonal',234,157,'');
			$f['variedad_foto'] = imagen_URL($f['variedad_foto'],234,157);
		    }
		    else
		    {
			$lazy_img = imagen_URL('cargando_imagen_vertical',157,234,'');
			$f['variedad_foto'] = imagen_URL($f['variedad_foto'],157,234);
		    }
                }
                $URL = URL_SUFIJO_VITRINA.SEO(strip_tags($f['contenedor_titulo']).'-'.$f['codigo_producto']).'?variedad='.$f['codigo_variedad'];
                $bELEMENTOS .= '<div class="categoria-elemento '.($f['tiene_oferta'] > 0 ? 'categoria-elemento-oferta' : '').'">';
                $bELEMENTOS .= '<a class="enlace-elemento" '.(empty($f['contenedor_descripcion']) ? '' : 'tooltip="'.htmlentities(strip_tags($f['contenedor_descripcion']),ENT_QUOTES,'UTF-8').'.<hr /><b>Clic en la foto para ampliar</b>"').' href="'.$URL.'">';
		$bELEMENTOS .= '<div class="categoria-elemento-imagen">';
			$bELEMENTOS .= '<img alt="'.htmlentities(strip_tags($f['contenedor_titulo']),ENT_QUOTES,'UTF-8').'" ';
			if ( ($i) > 3 && !PLATAFORMA_MOBIL)
			{
				$bELEMENTOS .= 'class="categoria-elemento-foto lazy" src="'.$lazy_img.'" data-original="'.$f['variedad_foto'].'" ';
			} else {
				$bELEMENTOS .= 'class="categoria-elemento-foto" src="'.$f['variedad_foto'].'" ';
			}
			
			$bELEMENTOS .= '/>';
			if ($f['tiene_oferta'] > 0)
				$bELEMENTOS .= '<div class="categoria-elemento-en-oferta">EN OFERTA</div>';
		$bELEMENTOS .= '</div>'; // categoria-elemento-imagen
		
                $bELEMENTOS .= '<div class="titulo">#'.$f['codigo_producto'].' - '.strip_tags($f['contenedor_titulo']).'</div>';
		$bELEMENTOS .= '<div class="precio">'.$f['precio_combinado'].'</div>';
		if ($f['tiene_oferta'] > 0)
			$bELEMENTOS .= '<div class="precio_oferta">Precio de oferta: '.$f['precio_oferta_combinado'].'</div>';
		$bELEMENTOS .= '<div class="categoria-elemento-clic-aqui">ampliar</div>';
		$bELEMENTOS .= '</a>';
                
                if(isset($_GET['preparacion']))
                {
                    if (isset($_GET['no_cantidad']))
                        $f['variedad_receta'] = preg_replace(array('/[^\w,\s]/','/\d\s{0,1}/'),'',$f['variedad_receta']);
                    $bELEMENTOS .= '<center><div style="text-align:center;width:133px;height:60px;">'.$f['variedad_receta'].'</div></center>';
                }
                $bELEMENTOS .= '</div>';
            }
        $bELEMENTOS .= '</td>';
        }
        $bELEMENTOS .= '</tr>';
    }
    $bELEMENTOS .= '</table>';
    
    return $bELEMENTOS;
}
?>
