<?php
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * GeometryCollection.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_collection_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * GeometryCollection.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_collection_create($params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * GeometryCollection.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_collection_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * GeometryCollection.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_collection_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * GeometryCollection.archive API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_collection_archive_spec(&$spec) {
  $spec['id']['api.required'] = 1;
  $spec['id']['title'] = E::ts('Geometry Collection ID');
  $spec['id']['type'] = CRM_Utils_Type::T_STRING;
}

/**
 * GeometryCollection.archive API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_collection_archive($params) {
  $result = CRM_CiviGeometry_BAO_GeometryCollection::archiveCollection($params);
  $apiResult = [];
  _civicrm_api3_object_to_array($result, $apiResult[$result->id]);
  return civicrm_api3_create_success($apiResult, $params);
}

/**
 * GeometryCollection.unarchive API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_collection_unarchive_spec(&$spec) {
  $spec['id']['api.required'] = 1;
  $spec['id']['title'] = E::ts('Geometry Collection ID');
  $spec['id']['type'] = CRM_Utils_Type::T_STRING;
}

/**
 * GeometryCollection.unarchive API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_collection_unarchive($params) {
  $archiveCheck = civicrm_api3('GeometryCollection', 'get', ['id' => $params['id']]);
  if (empty($archiveCheck['values'][$archiveCheck['id']]['is_archived'])) {
    throw new \API_Exception(E::ts("Cannot unarchive a geometry collection that is not archived"));
  }
  $result = CRM_CiviGeometry_BAO_GeometryCollection::unarchiveCollection($params);
  $apiResult = [];
  _civicrm_api3_object_to_array($result, $apiResult[$result->id]);
  return civicrm_api3_create_success($apiResult, $params);
}
