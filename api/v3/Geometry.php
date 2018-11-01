<?php
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Geometry.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_create_spec(&$spec) {
  $spec['collection_id']['title'] = ts('Collection');
  $spec['collection_id']['api.required'] = 1;
}

/**
 * Geometry.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_create($params) {
  if (!empty($params['collection_id']) && !is_array($params['collection_id'])) {
    throw new \API_Exception('Collection ID(s) needs to be passed as an array');
  }
  if (empty($params['id']) && empty($params['geometry'])) {
    throw new \API_Exception('Geometry is required unless supplying an id to do an update');
  }
  if (isset($params['geomety']) && empty($params['geometry'])) {
    throw new \API_Exception('Geometry was empty');
  }
  if (isset($params['geometry'])) {
    $json = json_decode($params['geometry']);
    if ($json === NULL) {
      throw new \API_Exception('Geometry is not proper GeoJSON');
    }
  }
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Geometry.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Geometry.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
