<?php
require dirname(__FILE__).DIRECTORY_SEPARATOR.'OpenErpWebServiceClient.php';
/*
 * This file contains classes required to connect to OpenERP-OCS module via werservices
 */

class OpenErpPartner extends OpenErpObject {
    protected function getClassName() {
        return 'res.partner';
    }

    protected function getAttributesMetadata() {
        return array (
            'name' => array('compulsory' => 1, 'references' => FALSE),
            'ref' => array('compulsory' => 1, 'references' => FALSE),
        );
    }

    protected function processAttributes() {}
}

class OpenErpPartnerAddress extends OpenErpObject {
    protected function getClassName() {
        return 'res.partner.address';
    }

    protected function getAttributesMetadata() {
        return array(
            'name' => array('compulsory' => 1, 'references' => FALSE),
            'document_type' => array('compulsory' => 1, 'references' => FALSE),
            'document_id' => array('compulsory' => 1, 'references' => FALSE),
            'name' => array('compulsory' => 1, 'references' => FALSE),
            'last_name' => array('compulsory' => 1, 'references' => FALSE),
            'gender' => array('compulsory' => 0, 'references' => FALSE),
            'partner_id' => array('compulsory' => 0, 'references' => array('classname' => 'OpenErpPartner','search_key' => 'ref')),
            'function' => array('compulsory' => 0, 'references' => FALSE),
            'street' => array('compulsory' => 0, 'references' => FALSE),
            'phone' => array('compulsory' => 0, 'references' => FALSE),
            'fax' => array('compulsory' => 0, 'references' => FALSE),
            'mobile' => array('compulsory' => 0, 'references' => FALSE),
            'email' => array('compulsory' => 0, 'references' => FALSE),
            'facebook' => array('compulsory' => 0, 'references' => FALSE),
            'twitter' => array('compulsory' => 0, 'references' => FALSE),
            'fax' => array('compulsory' => 0, 'references' => FALSE),
        );
    }

    protected function processAttributes() {}
}


class OpenErpPqr extends OpenErpObject {
    protected function getClassName() {
        return 'crm.claim';
    }

    protected function getAttributesMetadata() {
        return array(
            'partner_id' => array('compulsory' => 0, 'references' => array('classname' => 'OpenErpPartner','search_key' => 'ref')),
            'partner_address_id' => array('compulsory' => 0, 'references' => array('classname' => 'OpenErpPartnerAddress','search_key' => 'document_id')),
            'categ_id' => array('compulsory' => 1, 'references' => array('classname' => 'OpenErpOcsCategory')),
            'classification_id' => array('compulsory' => 1, 'references' => array('classname' => 'OpenErpOcsClassification')),
            'sub_classification_id' => array('compulsory' => 1, 'references' => array('classname' => 'OpenErpOcsClassification')),
            'description' => array('compulsory' => 1, 'references' => FALSE),
            'state' => array('compulsory' => 1, 'references' => FALSE),
            'csp_id' => array('compulsory' => 1, 'references' => array('classname' => 'OpenErpOcsAttentionPoint')),
            'channel' => array('compulsory' => 1, 'references' => array('classname' => 'OpenErpOcsChannel')),
            'external_dms_id' => array('compulsory' => 1, 'references' => FALSE),
            'priority' => array('compulsory' => 1, 'references' => FALSE),
        );
    }

    /**
     * Find PQR by Document Management System ID and load it
     */
    public function fetchOneByDmsId($value){
        return $this->fetchOne(array(array('external_dms_id','=',$value)));
    }

    /**
     *  Return a geojson featrue structure as array or in geojson format.
     *  Return null if PQR has not a geo_point
     */
    public function getGeoJsonFeature($as_array = false) {
        // { "type": "Feature",
        //   "geometry": {"type": "Point", "coordinates": [102.0, 0.5]},
        //   "properties": {"prop0": "value0"}
        // }
        if(empty($this->attributes['geo_point'])) return null;

        $feature = array(
            'type' => 'Feature',
            'geometry' => json_decode($this->attributes['geo_point']),
            'properties' => array(
                'category' => $this->attributes['categ_id'][1],
                'classification' => $this->attributes['sub_classification_id'][1],
                'description' => $this->attributes['description'],
                'subject' => $this->attributes['name'],
            )
        );
        if($as_array) {
            return $feature;
        }
        return json_encode($feature);
    }
}

class OpenErpOcsCategory extends OpenErpObject {
    protected function getClassName() {
        return 'crm.case.categ';
    }

    protected function getAttributesMetadata() {
        return array(
            'name' => array('compulsory' => 1, 'references' => FALSE),
        );
    }
}
class OpenErpOcsClassification extends OpenErpObject {
    protected function getClassName() {
        return 'ocs.claim_classification';
    }

    protected function getAttributesMetadata() {
        return array(
            'name' => array('compulsory' => 1, 'references' => FALSE),
            'parent_id' => array('compulsory' => 0, 'references' => array('classname' => 'OpenErpOcsClassification')),
        );
    }
}

class OpenErpOcsAttentionPoint extends OpenErpObject {
    protected function getClassName() {
        return 'ocs.citizen_service_point';
    }

    protected function getAttributesMetadata() {
        return array(
            'name' => array('compulsory' => 1, 'references' => FALSE),
        );
    }
}

class OpenErpOcsChannel extends OpenErpObject {
    protected function getClassName() {
        return 'crm.case.channel';
    }

    protected function getAttributesMetadata() {
        return array(
            'name' => array('compulsory' => 1, 'references' => FALSE),
        );
    }
}