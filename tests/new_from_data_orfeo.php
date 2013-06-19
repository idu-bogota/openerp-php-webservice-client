<?php
require dirname(__FILE__).'/../externals/testmore-php/testmore.php';
require(dirname(__FILE__).'/../config.inc.php');

plan('no_plan');
/** Incluir Libreria con el código del cliente del servicio web **/
require_ok(dirname(__FILE__).'/../src/OpenErpOcs.php'); 

$time = time();
diag($openerp_server);
diag("time = $time");
diag("username = $username");
diag("db = $dbname");

/**
 * CLASE para radicar PQRs extiende de la clase OpenERP PQR y especializa la metadata que es utilizada
 * para realizar validaciones del lado del cliente
 **/
class myOpenErpPqr extends OpenErpPqr {
    protected $create_operation_name = 'new_from_data';

    protected function getAttributesMetadata() {
        $metadata = parent::getAttributesMetadata();
        $metadata['state']['compulsory'] = false;
        $metadata['priority']['compulsory'] = false;
        $metadata['tipo_requerimiento_id'] = array('compulsory' => 1, 'references' => FALSE);
        $metadata['subcriterio_id'] = array('compulsory' => 1, 'references' => FALSE);
        $metadata['medio_recepcion_id'] = array('compulsory' => 1, 'references' => FALSE);
        $metadata['accion_juridica_id'] = array('compulsory' => 1, 'references' => FALSE);
        return $metadata;
    }

    protected function processAttributes() {
        $this->attributes['categ_id'] = $this->attributes['tipo_requerimiento_id'];
        $this->attributes['sub_classification_id'] = $this->attributes['subcriterio_id'];
        $this->attributes['channel'] = $this->attributes['medio_recepcion_id'];
    }
}

/** Arreglo de datos a ser usados en la radicación **/
$data = array (
  'partner_address_id' => //Equivalente a interesado, ciudadano que radica un requerimiento (Opcional)
  array (
    'name' => $time, //Nombres
    'last_name' => $time,//Apellidos
    'document_type' => 'CC',//Tipo de documento de identificación - ('CC','Cédula de ciudadanía'),('TI','Tarjeta de Identidad'),('Pasaporte','Pasaporte'),('CE','Cedula Extranjería')
    'document_number' => $time,//Número del documento de identidad
    'email' => $time.'@my.email.com', //Email de contacto
    'street' => 'KR 10 10 10',//Dirección de contacto
    'phone' => '12345',//Número de Teléfono
    'nombre_barrio' => 'patio bonito', //Nombre del barrio
    'nombre_localidad' => 'kennedy',//Nombre de la localidad
    'gender' => 'f',//Genero
  ),
  'partner_id' => array(//Si la radicación se hace a nombre de una persona juridica (opcional)
    'name' => 'empresa',//Nombre de la empresa
    'vat' => 'nit_#',//NIT
  ),
  'csp_id' => 1, //Punto de atención 1 corresponde a la sede del IDU calle 22 (obligatorio)
  'tipo_requerimiento_id' => 1,//Enviar el ID del sistema orfeo para el tipo de requerimiento (obligatorio)
  'subcriterio_id' => 1,//Enviar el ID del sistema orfeo para el subcriterio (obligatorio)
  'medio_recepcion_id' => 1,//Enviar el ID del sistema orfeo para el medio de recepción (obligatorio)
  'accion_juridica_id' => 1,//Enviar el ID del sistema orfeo para la acción jurídica (obligatorio)
  'orfeo_id' => $time,//Enviar el número de radicado orfeo (obligatorio)
  'description' => 'lorem ipsum',//Enviar la descripción del requerimiento (obligatorio)
  'claim_address' => 'KR 8 D 10 30',//Enviar la dirección del requerimiento, ej, la dirección de un daño en la malla vial 
  'nombre_barrio' => 'patio bonito',//Enviar el nombre del barrio correspondiente al requerimiento
  'nombre_localidad' => 'kennedy',//Enviar el nombre de la localidad correspondiente al requerimiento
);

/** Conexion al servidor OpenERP **/
$c = new OpenErpWebServiceClient($openerp_server, $username, $pwd, $dbname);

/** Radicar **/
$new_pqr = new myOpenErpPqr($c);
$new_pqr->attributes = $data;
$result = $new_pqr->create();

/** Prueba del resultado **/
var_export($result);
ok($result['status'] == 'success', 'Success');
ok($result['result']['id'] > 0, 'Object Created');
