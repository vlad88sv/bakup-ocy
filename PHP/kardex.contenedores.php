<?php
echo '<h1>Mantenimiento de contenedores</h1>';
echo '<form action="'.PROY_URL_ACTUAL.'" method="get" >';
echo '<table class="tabla-estandar opsal_tabla_borde_oscuro opsal_tabla_ancha tabla-centrada">';
echo '<tr>';
echo '<th>Listar contenedores</th>';
echo '<th>Búscar contenedor</th>';
echo '</tr>';
echo '<tr>';
echo '<td><p>Contenedores en patio '. ui_combobox('patio_virtual', '<option value="">Todos</option>' . db_ui_opciones('id', 'nombre', 'virt_patios')).'<input type="submit" value="Ver"></p></td>';
echo '<td><input type="text" name="buscar_contenedor" placeholder="Cód. contenedor" /><input type="submit" value="Búscar" /></td>';
echo '</tr>';
echo '</table>';
echo '</form>';

echo '<br /><hr /><br />';


// --------------------- FILTROS

$__filtros = '';

// --------------------- PME

$opts['dbh'] = db_obtener_link_legado();
$opts['page_name'] = PROY_URL_ACTUAL;
$opts['tb'] = 'virt_contenedor';

// Name of field which is the unique key
$opts['key'] = 'id';

// Type of key field (int/real/string/date etc.)
$opts['key_type'] = 'int';

// Sorting field(s)
$opts['sort_field'] = array('id');

// Number of records to display on the screen
// Value of -1 lists all records in a table
$opts['inc'] = 15;

// Options you wish to give the users
// A - add,  C - change, P - copy, V - view, D - delete,
// F - filter, I - initial sort suppressed
$opts['options'] = 'ACPVDF';

// Number of lines to display on multiple selection filters
$opts['multiple'] = '4';

// Navigation style: B - buttons (default), T - text links, G - graphic links
// Buttons position: U - up, D - down (default)
$opts['navigation'] = 'UB';

// Display special page elements
$opts['display'] = array(
	'form'  => true,
	'query' => true,
	'sort'  => true,
	'time'  => true,
	'tabs'  => true
);

// Set default prefixes for variables
$opts['js']['prefix']               = 'PME_js_';
$opts['dhtml']['prefix']            = 'PME_dhtml_';
$opts['cgi']['prefix']['operation'] = 'PME_op_';
$opts['cgi']['prefix']['sys']       = 'PME_sys_';
$opts['cgi']['prefix']['data']      = 'PME_data_';

/* Get the user's default language and use it if possible or you can
   specify particular one you want to use. Refer to official documentation
   for list of available languages. */
$opts['language'] = 'ES-AR-UTF8';

/* Table-level filter capability. If set, it is included in the WHERE clause
   of any generated SELECT statement in SQL query. This gives you ability to
   work only with subset of data from table.
*/

$opts['filters'] = $__filtros;


$opts['fdd']['id'] = array(
  'name'     => 'ID',
  'select'   => 'T',
  'options'  => 'AVCPDR', // auto increment
  'maxlen'   => 11,
  'default'  => '0',
  'sort'     => true
);

$opts['fdd']['codigo_agencia'] = array(
  'name'     => 'Codigo agencia',
  'select'   => 'T',
  'maxlen'   => 11,
  'sort'     => true
);

$opts['fdd']['codigo_agencia']['values']['table'] = 'opsal_usuarios';
$opts['fdd']['codigo_agencia']['values']['column'] = 'codigo_usuario';
$opts['fdd']['codigo_agencia']['values']['description']['columns'][0] = 'usuario';
$opts['fdd']['codigo_agencia']['values']['filters'] = 'nivel="agencia"'; 
$opts['fdd']['codigo_agencia']['values']['orderby'] = 'usuario'; 


$opts['fdd']['codigo_contenedor'] = array(
  'name'     => 'Codigo contenedor',
  'select'   => 'T',
  'maxlen'   => 60,
  'sort'     => true
);
$opts['fdd']['tam_contenedor'] = array(
  'name'     => 'Tamaño contenedor',
  'select'   => 'T',
  'maxlen'   => 4,
  'sort'     => true,
  'values'   => array ('20', '40', '45', '48'),
);
$opts['fdd']['tipo_contenedor'] = array(
  'name'     => 'Tipo contenedor',
  'select'   => 'T',
  'maxlen'   => 6,
  'sort'     => true,
  'values'   => array ('DC', 'HC', 'OT', 'RF', 'FR', 'TQ')
);
    
$opts['fdd']['tara'] = array(
  'name'     => 'Tara',
  'select'   => 'T',
  'maxlen'   => 12,
  'sort'     => true
);
$opts['fdd']['chasis'] = array(
  'name'     => 'Chasis',
  'select'   => 'T',
  'maxlen'   => 300,
  'sort'     => true
);

$opts['fdd']['estado'] = array(
  'name'     => 'Estado',
  'select'   => 'T',
  'maxlen'   => 18,
  'values'   => array(
                  "dentro",
                  "fuera"),
  'sort'     => true
);
$opts['fdd']['fechatiempo_ingreso'] = array(
  'name'     => 'Fechatiempo ingreso',
  'select'   => 'T',
  'options'  => 'AVCPDRH', // updated automatically (MySQL feature)
  'maxlen'   => 19,
  'sort'     => true
);
$opts['fdd']['fechatiempo_egreso'] = array(
  'name'     => 'Fechatiempo egreso',
  'select'   => 'T',
  'options'  => 'AVCPDH',
  'maxlen'   => 19,
  'sort'     => true
);
$opts['fdd']['ingresado_por'] = array(
  'name'     => 'Ingresado por',
  'select'   => 'T',
  'maxlen'   => 11,
  'sort'     => true
);

$opts['fdd']['ingresado_por']['values']['table'] = 'opsal_usuarios';
$opts['fdd']['ingresado_por']['values']['column'] = 'codigo_usuario';
$opts['fdd']['ingresado_por']['values']['description']['columns'][0] = 'usuario';


$opts['fdd']['egresado_por'] = array(
  'name'     => 'Egresado por',
  'select'   => 'T',
  'maxlen'   => 11,
  'sort'     => true
);

$opts['fdd']['egresado_por']['values']['table'] = 'opsal_usuarios';
$opts['fdd']['egresado_por']['values']['column'] = 'codigo_usuario';
$opts['fdd']['egresado_por']['values']['description']['columns'][0] = 'usuario';

// Now important call to phpMyEdit
new phpMyEdit($opts);
