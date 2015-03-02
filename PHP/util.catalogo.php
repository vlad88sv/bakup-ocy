<?php
$opts['dbh'] = db_obtener_link_legado();
$opts['page_name'] = PROY_URL_ACTUAL_DINAMICA;
$opts['tb'] = 'inv_catalogo';

if (!empty($_GET['filtro'])) {
    $opts['filters'] = 'tipo="'.db_codex ($_GET['filtro']).'"';
} else {
    echo '<p>No se seleccionó un filtro especifico.</p>';
    return;
}

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
$opts['navigation'] = 'DB';

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

$opts['language'] = 'ES-AR-UTF8';

$opts['fdd']['id'] = array(
  'name'     => 'ID',
  'select'   => 'T',
  'options'  => 'AVCPDR', // auto increment
  'maxlen'   => 10,
  'default'  => '0',
  'sort'     => true
);
$opts['fdd']['nombre'] = array(
  'name'     => 'Nombre de ' . @$_GET['filtro'],
  'select'   => 'T',
  'maxlen'   => 300,
  'sort'     => true
);
$opts['fdd']['descripcion'] = array(
  'name'     => 'Descripcion',
  'select'   => 'T',
  'maxlen'   => 196605,
  'textarea' => array(
    'rows' => 5,
    'cols' => 50),
  'sort'     => true
);
$opts['fdd']['facturacion'] = array(
  'name'     => 'Facturacion',
  'select'   => 'T',
  'maxlen'   => 18,
  'values'   => array(
                  "evento",
                  "hora"),
  'sort'     => true
);
$opts['fdd']['costo_bajo'] = array(
  'name'     => 'Costo 1',
  'select'   => 'T',
  'maxlen'   => 8,
  'sort'     => true
);
$opts['fdd']['costo_medio'] = array(
  'name'     => 'Costo 2',
  'select'   => 'T',
  'maxlen'   => 8,
  'sort'     => true
);
$opts['fdd']['costo_alto'] = array(
  'name'     => 'Costo 3',
  'select'   => 'T',
  'maxlen'   => 8,
  'sort'     => true
);
$opts['fdd']['tipo'] = array(
  'name'     => 'Tipo',
  'select'   => 'T',
  'maxlen'   => 6,
  'values'   => array(@$_GET['filtro']), //array("servicio","reparacion"),
  'sort'     => true
);
$opts['fdd']['habilitada'] = array(
  'name'     => 'Habilitada',
  'select'   => 'T',
  'maxlen'   => 6,
  'values'   => array(
                  "si",
                  "no"),
  'sort'     => true
);

// Now important call to phpMyEdit
new phpMyEdit($opts);