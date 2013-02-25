<?php
require dirname(__FILE__).'/../externals/testmore-php/testmore.php';
require(dirname(__FILE__).'/../config.inc.php');

plan('no_plan');
require_ok(dirname(__FILE__).'/../src/OpenErpOcs.php');

$time = time();
diag($openerp_server);
diag("time = $time");
diag("username = $username");
diag("db = $dbname");

$c = new OpenErpWebServiceClient($openerp_server, $username, $pwd, $dbname);

//***********************************************************
$partner_address_id = new OpenErpPartnerAddress($c);
$partner_address_id->attributes = array(
    'name' => 'citizen '.$time,
    'document_type' => 'CC',
    'document_number' => $time,
    'name' => 'name '.$time,
    'last_name' => 'lastname '.$time,
    'email' => $time.'@email.com',
    'gender' => 'm',
);

try {
    $id = $partner_address_id->create();
    ok($id > 0, 'Object Created');
}
catch(Exception $e) {
    fail('Object no created due: '. $e->getMessage());
    diag($e->getTraceAsString());
}

//***********************************************************
$partner_id = new OpenErpPartner($c);
$partner_id->attributes = array(
    'name' => 'partner '.$time,
    'ref' => $time
);

try {
    $id = $partner_id->create();
    ok($id > 0, 'Object Created');
}
catch(Exception $e) {
    fail('Object no created due: '. $e->getMessage());
    diag($e->getTraceAsString());
}

//***********************************************************
$pqr_id;
$pqr = new OpenErpPqr($c);
$pqr->attributes = array(
    'partner_id' => $partner_id,
    'partner_address_id' =>  $partner_address_id,
    'categ_id' => array('name' => 'Valor reclamaciones'),
    'classification_id' => array('name' => 'test '.$time),
    'sub_classification_id' => array('name' => 'sub test '.$time),
    'description' => 'This is a PQR '.$time,
    'state' => 'pending',
    'priority' => 'h',
    'csp_id' => 1,
    'channel' => array('name' => 'direct'),
    'orfeo_id' => $time
);

try {
    $pqr_id = $pqr->create();
    ok($pqr_id > 0, 'PQR created with ID:'.$pqr_id);
}
catch(Exception $e) {
    fail('PQR no created due: '. $e->getMessage());
    diag($e->getTraceAsString());
}

$pqr_load = new OpenErpPqr($c);
$pqr_load->loadOne($pqr_id);
is($pqr_load->id,$pqr_id,'ID found is OK' );
is($pqr_load->attributes['description'],'This is a PQR '.$time,'description is OK' );
is($pqr_load->attributes['state'],'pending','state is OK' );

$pqr_found = new OpenErpPqr($c);
$pqr_found->fetchOneByOrfeoId($time);
is($pqr_found->id,$pqr_id,'ID found is OK' );
is($pqr_found->attributes['description'],'This is a PQR '.$time,'description is OK' );
is($pqr_found->attributes['state'],'pending','state is OK' );
//var_export($pqr_found->attributes);

//***********************************************************
//Bogota -8250479.1376255,530328.03300816 | -8245663.6048442,509842.90943058

$lon = '-' . rand(8250479,8245663) .'.'. rand(0,99999999);
$lat = rand(509842,530328) .'.'. rand(0,99999999);
$geojson = sprintf('{"type":"Point","coordinates":[%s,%s]}',$lon, $lat);
diag($geojson);
$pqr = new OpenErpPqr($c);
$pqr->attributes = array(
    'partner_address_id' =>  array(
        'name' => 'citizen '.$time,
        'document_type' => 'CC',
        'document_number' => $time,
        'name' => 'name '.$time,
        'last_name' => 'lastname '.$time,
        'email' => $time.'@email.com.co',
    ),
    'partner_id' => array(
        'name' => 'my organisation '.$time,
        'ref' => 'nit_'.$time,
    ),
    'geo_point' => $geojson,
    'categ_id' => array('name' => 'Valor reclamaciones'),
    'priority' => 'h',
    'classification_id' => array('name' => 'test '.$time),
    'sub_classification_id' => array('name' => 'sub test '.$time),
    'csp_id' => 1,
    'description' => 'This is a PQR '.$time,
    'state' => 'pending',
    'channel' => array('name' => 'direct'),
    'orfeo_id' => $time,
    'damage_type_by_citizen' => 'hundimiento',
    'damage_width_by_citizen' => '10cm',
    'damage_length_by_citizen' => '1m',
    'damage_deep_by_citizen' => '1m',
);
try {
    $pqr_id = $pqr->create();
    ok($pqr_id > 0, 'PQR created with ID:'.$pqr_id);
}
catch(Exception $e) {
    fail('PQR no created due: '. $e->getMessage());
    diag($e->getTraceAsString());
}

$pqr_list = new OpenErpPqr($c);
$results = $pqr_list->fetch();
ok(count($results) >= 2, 'Listing no limiting is OK');

$results = $pqr_list->fetch(array(),0,1);
ok(count($results) == 1, 'Listing limiting to one is OK');

$data = array (
  'partner_address_id' =>
  array (
    'name' => $time,
    'last_name' => $time,
    'document_type' => 'CC',
    'document_number' => $time,
    'email' => $time.'@my.email.com',
    'gender' => 'f',
  ),
  'categ_id' => 1,
  'classification_id' => 1,
  'sub_classification_id' => 1,
  'csp_id' => 1,
  'channel' => 1,
  'orfeo_id' => '0',
  'priority' => 'l',
  'state' => 'pending',
  'description' => 'testing',
  'damage_type_by_citizen' => 'hundimiento',
  'damage_width_by_citizen' => 'ns-nr',
  'damage_length_by_citizen' => 'ns-nr',
  'damage_deep_by_citizen' => 'ns-nr',
  'geo_point' => '{"type":"Point","coordinates":[-8246435.1410983,512561.2012486]}',
  'email_from' => $time.'@my.email.com',
);

$result = $c->execute('crm.claim', 'new_from_data', $data);
#var_export($result);
ok($result['status'] == 'success', 'Success');
ok($result['result']['id'] > 0, 'Object Created');

class myOpenErpPqr extends OpenErpPqr {
    protected $create_operation_name = 'new_from_data';

    protected function processAttributes() {
    }
}

$new_pqr = new myOpenErpPqr($c);
$new_pqr->attributes = $data;
$result = $new_pqr->create();
#var_export($result);
ok($result['status'] == 'success', 'Success');
ok($result['result']['id'] > 0, 'Object Created');

// $client = new Zend_XmlRpc_Client($openerp_server.'/object');
// $results = $client->call('execute', array($dbname, 1, 'admin1', 'crm.claim', 'search', array(),0,2));
// var_export($results);

// $client = new Zend_XmlRpc_Client($openerp_server.'/object');
// $data = array(
//   'categ_id' => 16,
//   'priority' => 'l',
//   'classification_id' => 5,
//   'sub_classification_id' => 6,
//   'csp_id' => 1,
//   'state' => 'draft',
//   'channel' => 3,
//   'description' => 'test',
// );
// echo $id = $client->call('execute', array($dbname, 1, 'admin', 'crm.claim', 'create', $data));
