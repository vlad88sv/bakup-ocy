<?php
$opts['dbh'] = db_obtener_link_legado();
$opts['page_name'] = PROY_URL_ACTUAL_DINAMICA;
$opts['tb'] = 'inv_inventariable';

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

/* Get the user's default language and use it if possible or you can
   specify particular one you want to use. Refer to official documentation
   for list of available languages. */
$opts['language'] = 'ES-AR-UTF8';


/* Table-level filter capability. If set, it is included in the WHERE clause
   of any generated SELECT statement in SQL query. This gives you ability to
   work only with subset of data from table.

$opts['filters'] = "column1 like '%11%' AND column2<17";
$opts['filters'] = "section_id = 9";
$opts['filters'] = "PMEtable0.sessions_count > 200";
*/

/* Field definitions
   
Fields will be displayed left to right on the screen in the order in which they
appear in generated list. Here are some most used field options documented.

['name'] is the title used for column headings, etc.;
['maxlen'] maximum length to display add/edit/search input boxes
['trimlen'] maximum length of string content to display in row listing
['width'] is an optional display width specification for the column
          e.g.  ['width'] = '100px';
['mask'] a string that is used by sprintf() to format field output
['sort'] true or false; means the users may sort the display on this column
['strip_tags'] true or false; whether to strip tags from content
['nowrap'] true or false; whether this field should get a NOWRAP
['select'] T - text, N - numeric, D - drop-down, M - multiple selection
['options'] optional parameter to control whether a field is displayed
  L - list, F - filter, A - add, C - change, P - copy, D - delete, V - view
            Another flags are:
            R - indicates that a field is read only
            W - indicates that a field is a password field
            H - indicates that a field is to be hidden and marked as hidden
['URL'] is used to make a field 'clickable' in the display
        e.g.: 'mailto:$value', 'http://$value' or '$page?stuff';
['URLtarget']  HTML target link specification (for example: _blank)
['textarea']['rows'] and/or ['textarea']['cols']
  specifies a textarea is to be used to give multi-line input
  e.g. ['textarea']['rows'] = 5; ['textarea']['cols'] = 10
['values'] restricts user input to the specified constants,
           e.g. ['values'] = array('A','B','C') or ['values'] = range(1,99)
['values']['table'] and ['values']['column'] restricts user input
  to the values found in the specified column of another table
['values']['description'] = 'desc_column'
  The optional ['values']['description'] field allows the value(s) displayed
  to the user to be different to those in the ['values']['column'] field.
  This is useful for giving more meaning to column values. Multiple
  descriptions fields are also possible. Check documentation for this.
*/

$opts['fdd']['id'] = array(
  'name'     => 'ID',
  'select'   => 'T',
  'options'  => 'AVCPDR', // auto increment
  'maxlen'   => 10,
  'default'  => '0',
  'sort'     => true
);
$opts['fdd']['nombre'] = array(
  'name'     => 'Nombre',
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
if (@$_GET['filtro'] == 'material') {

    $opts['fdd']['unidad'] = array(
      'name'     => 'Unidad',
      'select'   => 'T',
      'maxlen'   => 9,
      'values'   => array("u","lb","kg","L","gal","oz","g","m"),
      'sort'     => true
    );

}
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
  'values'   => array(@$_GET['filtro']), //array("equipo","material"),
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

