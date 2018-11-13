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
  $spec['collection_id']['title'] = E::ts('Collection');
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
    if (!CRM_Utils_Rule::commaSeparatedIntegers($params['collection_id'])) {
      throw new \API_Exception(E::ts('collection_id is not a valid list of ids'));
    }
    $params['collection_id'] = explode(',', $params['collection_id'];
  }
  if (empty($params['id']) && empty($params['geometry'])) {
    throw new \API_Exception(E::ts('Geometry is required unless supplying an id to do an update'));
  }
  if (isset($params['geomety']) && empty($params['geometry'])) {
    throw new \API_Exception(E::ts('Geometry was empty'));
  }
  if (isset($params['geometry'])) {
    $json = json_decode($params['geometry']);
    if ($json === NULL) {
      throw new \API_Exception(E::ts('Geometry is not proper GeoJSON'));
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

/**
 * Geomety.getCollections
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_getcollection($params) {
  return _civicrm_api3_basic_get('CRM_CiviGeometry_BAO_GeometryCollectionGeometry', $params);
}

/**
 * Geometry.getcollections API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_getcollection_spec(&$spec) {
  $spec['geometry_id']['title'] = E::ts('Geometry');
  $spec['geometry_id']['api.required'] = 1;
  $spec['geometry_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['collection_id']['title'] = E::ts('Collection IDs');
}

/**
 * Geomety.aCollections
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_addcollection($params) {
  if (!is_array($params['collection_id'])) {
    throw new \API_Exception('Collection Id must be an Array');
  }
  $result = CRM_CiviGeometry_BAO_Geometry::addGeometryToCollection($params);
  return civicrm_api3_create_success($result, $params);
}

/**
 * Geometry.getcollections API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_addcollection_spec(&$spec) {
  $spec['geometry_id']['title'] = E::ts('Geometry');
  $spec['geometry_id']['api.required'] = 1;
  $spec['geometry_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['collection_id']['title'] = E::ts('Collection IDs');
}


/**
 * Geometry.removeCollection
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_removecollection($params) {}
