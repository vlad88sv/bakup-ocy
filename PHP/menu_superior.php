<table style="width:100%;">
<tbody>
<tr>
<td>
    <a href="/">
<?php
switch (MODO)
{
    case MODO_MYR:
        echo '<img src="/IMG/general/cabecera_myr.png" />';
        break;
    
    case MODO_OCY:
    default:
        echo '<img src="/IMG/general/cabecera.jpg" />';
        break;
}
?>
    </a>
</td>
<td>

    <?php if (S_iniciado() && _F_usuario_cache('nivel') == 'tecnico'): ?>
    <input type="text" id="traducir" style="width:50px" value="" /><input id="ejecutar_traduccion" type="button" value="->"  style="padding:0px" /><input type="text" id="traducido" style="width:50px" value="" />
    <?php endif; ?>

</td>
<td style="text-align: right;">
<img style="vertical-align: middle;" class="cambio_lenguaje" rel="es_SV" title="Cambiar idioma a español" src="/IMG/stock/flag_sv.png" />
<img style="vertical-align: middle;" class="cambio_lenguaje" rel="en_US" title="Cambiar idioma a inglés" src="/IMG/stock/flag_us.png" />
<?php if (S_iniciado()): ?>
<img style="vertical-align: middle;" title="Imprimir esta vista" onclick="window.print()" src="/IMG/general/imprimir.gif" />
<a class="boton" href="/finalizar.html">Cerrar sesión</a>
<?php endif; ?>
</td>
</tr>
</tbody>
</table>
<?php
if (!S_iniciado())
{
    echo '<hr />';
    return;
}


// ************ traducible ******* //
if (S_iniciado() && _F_usuario_cache('nivel') == 'agencia')
{
    echo '
    <ul id="nav" class="dropdown dropdown-horizontal">
    <li><a href="/" title="'._('Contenedores').'">'._('Contenedores').'</a></li>
    
    <li><a href="#" onclick="return false;" title="'._('Módulo de reportes').'">'._('Reportes').'</a>
    <ul>
	<li><a href="/control.patio.html" title="'._('Control patio').'">'._('Reporte de patio').'</a>
	<li><a href="/control.ingresos.html" title="'._('Control ingresos').'">'._('Reporte recepciones').'</a>
	<li><a href="/control.remociones.html" title="'._('Control remociones').'">'._('Reporte remociones').'</a>
	<li><a href="/control.embarques.html" title="'._('Control embarques').'">'._('Reporte embarques').'</a>
	<li><a href="/control.salidas.html" title="'._('Control salidas').'">'._('Reporte despachos').'</a>
        <li><a href="/control.combinado.html" title="'._('Control combinado (ingresos+salidas)').'">'._('Reporte combinado').'</a>            
    </ul>
    </li>
    
    <li><a href="/contenedores.html" title="'._('Módulo de patio').'">'._('Patio').'</a></li>
    
    <li id="buscador">
    <form id="frm_buscar">
	<input name="busqueda" type="text" id="busqueda" value="" />
	<input type="submit" id="buscar" value="Búscar" />
    </form>
    </li>
    </ul>
    ';
    return;
}
// ************ traducible ******* //

if (S_iniciado() && _F_usuario_cache('nivel') == 'externo')
{
    echo '
    <ul id="nav" class="dropdown dropdown-horizontal">
    <li><a href="#" onclick="return false;" title="'._('Módulo de mantenimiento').'">'._('OPSAL').'</a>
    <ul>
        <li><a href="/control.reparaciones.html" title="'._('Control reparaciones').'">'._('Reporte de reparaciones').'</a></li>
        <li><a href="/control.patio.html" title="'._('Control patio').'">'._('Reporte de patio').'</a></li>
        <li><a href="/contenedores.html" title="'._('Módulo de patio').'">'._('Patio').'</a></li>
    </ul>
    </li>

    <li><a href="#" onclick="return false;" title="'._('Módulo de mantenimiento').'">'._('Catálogos').'</a>
    <ul>
	<li><a href="/util.catalogo.html?filtro=servicio">'._('Servicios').'</a>
	<li><a href="/util.catalogo.html?filtro=reparacion">'._('Reparaciones').'</a>	
        <li><a href="/util.inventariable.html?filtro=equipo">'._('Equipos + inventario').'</a>
        <li><a href="/util.inventariable.html?filtro=material">'._('Materiales´+ inventario').'</a>
    </ul>
    </li>

    <li><a href="#" onclick="return false;" title="'._('Módulo de KARDEX').'">'._('KARDEX').'</a>
    <ul>
        <li><a href="/kardex.contenedores.html">'._('Contenedores').'</a>
	<li><a href="/kardex.cabezal.html">'._('Cabezales').'</a>
	<li><a href="/kardex.chasis.html">'._('Chasis').'</a>
	<li><a href="/kardex.furgon.html">'._('Furgones').'</a>
	<li><a href="/kardex.genset.html">'._('Gensets').'</a>
    </ul>
    </li>

    <li><a href="#" onclick="return false;">'._('Servicios').'</a>
    <ul>
	<li><a href="/administracion?tpl=patios.virtuales.html">'._('Utilidades').'</a>
    </ul>
    </li>

    <li><a href="#" onclick="return false;" title="'._('Módulo de reportes').'">'._('Reportes').'</a>
    <ul>
	<li><a href="/reportes.myr.contenedores.html">'._('Reporte de contenedores').'</a>
	<li><a href="/reportes.myr.cotizaciones.html">'._('Reporte de cotizaciones').'</a>
	<li><a href="/reportes.myr.trabajos.html">'._('Reporte de trabajos').'</a>
	<li><a href="/reportes.myr.cabezales.html">'._('Reporte de Cabezales').'</a>
        <li><a href="/reportes.myr.chasis.html">'._('Reporte de Chasis').'</a>
	<li><a href="/reportes.myr.furgones.html">'._('Reporte de Furgones').'</a>
	<li><a href="/reportes.myr.gensets.html">'._('Reporte de Gensets').'</a>
	<li><a href="/reportes.myr.herramientas.html">'._('Reporte de Herramientas').'</a>
	<li><a href="/reportes.myr.inventario.html">'._('Reporte de Inventario').'</a>
    </ul>
    </li>

    <li><a href="#" onclick="return false;">'._('Administración').'</a>
    <ul>
	<li><a href="/admin.patio.virtual.html">'._('Patios virtuales').'</a>
	<li><a href="/admin.ext.usuarios.html">'._('Usuarios').'</a>
    </ul>
    </li>
    
    
    <li id="buscador">
    <form id="frm_buscar">
	<input name="busqueda" type="text" id="busqueda" value="" />
	<input type="submit" id="buscar" value="Búscar" />
    </form>
    </li>
    </ul>
    ';
    return;
}
?>
<ul id="nav" class="dropdown dropdown-horizontal">
<li><a href="/" title="Alertas">Alertas</a></li>
<li><a href="/contenedores.html" title="Módulo de contenedores">Contenedores</a>
    <ul>
        <li><a href="/control.salidas.bloque.html" title="Salidas en bloque">Reporte salidas en bloque</a>
	<li><a href="/control.patio.html" title="Control patio">Reporte de patio</a>
        <li><a href="/control.combinado.html" title="Control combinado (ingresos+salidas)">Reporte combinado</a>
	<li><a href="/control.ingresos.html" title="Control ingresos">Reporte recepciones </a>
	<li><a href="/control.remociones.html" title="Control remociones">Reporte remociones</a>
	<li><a href="/control.doble.movimientos.html" title="Control doble movimientos">Reporte doble movimientos</a>
	<li><a href="/control.embarques.html" title="Control embarques">Reporte embarques</a>
	<li><a href="/control.salidas.html" title="Control salidas">Reporte despachos</a>
	<li><a href="/control.consolidado.html" title="Consolidado de año">Consolidado de año</a>
	<li><a href="/control.consolidado.agencia.html" title="Consolidado de agencia">Consolidado de agencia</a>
    </ul>
</li>
<li><a href="/elaboracion.de.condicion.html" title="Módulo de contenedores">E. Condición</a>
    <ul>
	<li><a href="/control.elaboracion.de.condicion.html" title="Reporte de condiciones">Obtener reporte</a>
    </ul>
</li>
<li><a href="/supervision.carga.descarga.html" title="Supervisión de carga y descarga">Supervisión OPS C/D</a>
    <?php if (_F_usuario_cache('nivel') == 'jefatura'): ?>
    <ul>
	<li><a href="/control.supervision.carga.descarga.html">Reportes y facturación</a>
    </ul>
    <?php endif; ?>
</li>
<li><a href="/lineas.de.amarre.html" title="Módulo de contenedores">Líneas de amarre</a>
    <ul>
        <li><a href="/control.lineas.de.amarre.html" title="Control de líneas de amare">Control</a></li>
    </ul>
</li>

<?php if (_F_usuario_cache('nivel') != 'jefatura' && _F_usuario_cache('modulo_facturar') == '1'): ?>
<li><a href="/facturacion.html" title="Módulo de facturacion">Facturación</a>
<?php endif; ?>

<?php if (_F_usuario_cache('nivel') == 'jefatura'): ?>
<li><a href="/facturacion.html" title="Módulo de facturacion">Facturación</a>
    <ul>
        <li><a href="/control.facturas.html" title="Control de facturas">Control</a></li>
	<li><a href="/control.estado.de.cuenta.html" title="Estado de cuenta">Estado de cuenta</a></li>
        <li><a href="/control.contador.html" title="Reporte contaduría">Reporte contaduría</a></li>
        <li><a href="/facturacion.personalizada.html" title="Facturas inventadas">Inventar factura</a></li>
    </ul>
</li>
<li><a href="/administracion.html" title="Módulo de administracion">Administrador</a>
    <ul>
        <li><a href="/reportes.html" title="Reportes">Estadísticas</a></li>
	<li><a href="/bitacora.html" title="Bitácora">Bitacora</a></li>
        <li><a href="/especial.cambiar.buque.html" title="Bitácora">Cambio de buque</a></li>
    </ul>
</li>
<?php else: ?>
<?php if (_F_usuario_cache('nivel') != 'jefatura' && _F_usuario_cache('modulo_facturar') == '1'): ?>
<li><a href="/administracion.html" title="Módulo de administracion">Administrador</a>
<?php endif; ?>
<li><a href="/reportes.html" title="Reportes">Estadísticas</a></li>
<li><a href="/bitacora.html" title="Bitácora">Bitacora</a></li>
<?php endif; ?>

<li id="buscador">
    <form id="frm_buscar">
	<input name="busqueda" type="text" id="busqueda" value="" />
	<input type="submit" id="buscar" value="Búscar" />
    </form>
</li>
</ul>
<noscript>
<div style="background-color:#fef1b9;font-size:14px;padding:10px;border-radius:10px;margin:10px 0px;text-align: center;">
Advertencia: su navegador no posee <b>JavaScript</b>, por lo que su experiencia no será óptima.<br />
</div>
</noscript>