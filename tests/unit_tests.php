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
    'document_type' => 'C',
    'document_number' => $time,
    'name' => 'name '.$time,
    'last_name' => 'lastname '.$time,
    'email' => $time.'@email.com',
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
    'external_dms_id' => $time
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
$pqr_found->fetchOneByDmsId($time);
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
        'document_type' => 'C',
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
    'external_dms_id' => $time,
);
try {
    $pqr_id = $pqr->create();
    ok($pqr_id > 0, 'PQR created with ID:'.$pqr_id);
}
catch(Exception $e) {
    fail('PQR no created due: '. $e->getMessage());
    diag($e->getTraceAsString());
}

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
