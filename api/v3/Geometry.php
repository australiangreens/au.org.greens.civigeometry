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
  $spec['format']['title'] = E::ts('Geometry Data Format e.g. gzip');
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
    $params['collection_id'] = explode(',', $params['collection_id']);
  }
  if (empty($params['id']) && empty($params['geometry'])) {
    throw new \API_Exception(E::ts('Geometry is required unless supplying an id to do an update'));
  }
  if (isset($params['geomety']) && empty($params['geometry'])) {
    throw new \API_Exception(E::ts('Geometry was empty'));
  }
  if (isset($params['geometry'])) {
    if (isset($params['format'])) {
      if ($params['format'] == 'gzip') {
        try {
          $params['geometry'] = gzdecode($params['geometry']);
        }
        catch(Exception $e) {
          throw new API_Exception($e->getMessage());
        }
        $params['geometry'] = str_replace("'", '"', $params['geometry']);
      }
      elseif ($params['format'] == 'file') {
        if (!file_exists($params['geometry'])) {
          throw new \API_Exception(E::ts('File does not exist'));
        }
        $params['geometry'] = file_get_contents($params['geometry']);
      }
    }
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
  $results = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
  if (!empty($results['values'])) {
    foreach ($results['values'] as $id => $values) {
      $results['values'][$id]['geometry'] = CRM_Core_DAO::singleValueQuery("SELECT ST_AsGeoJSON(geometry) FROM civigeometry_geometry WHERE id = %1", [1 => [$id, 'Positive']]);
    }
  }
  return $results;
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
function civicrm_api3_geometry_removecollection($params) {
  return CRM_CiviGeometry_BAO_Geometry::removeGeometryFromCollection($params);
}

/**
 * Geometry.removecollections API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_removecollection_spec(&$spec) {
  $spec['geometry_id']['title'] = E::ts('Geometry');
  $spec['geometry_id']['api.required'] = 1;
  $spec['geometry_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['collection_id']['title'] = E::ts('Collection IDs');
  $spec['collection_id']['api.required'] = 1;
}

/**
 * Geomety.getcentroid
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_getcentroid($params) {
  $result = CRM_CiviGeometry_BAO_Geometry::getCentroid($params);
  return civicrm_api3_create_success($result);
}

/**
 * Geometry.getcentroid API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_getcentroid_spec(&$spec) {
  $spec['id']['title'] = E::ts('Geometry');
  $spec['id']['api.required'] = 1;
  $spec['id']['type'] = CRM_Utils_Type::T_INT;
}

/**
 * Geomety.archive
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_archive($params) {
  $apiResult = [];
  $result = CRM_CiviGeometry_BAO_Geometry::archiveGeometry($params);
  _civicrm_api3_object_to_array($result, $apiResult[$result->id]); 
  return civicrm_api3_create_success($apiResult, $params);
}

/**
 * Geometry.archive API specification (optional)
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_archive_spec(&$spec) {
  $spec['id']['title'] = E::ts('Geometry ID');
  $spec['id']['api.required'] = 1;
  $spec['id']['type'] = CRM_Utils_Type::T_INT;
}

/**
 * Geometry.contains
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_contains($params) {
  $paramsToTest = ['geometry_a', 'geometry_b'];
  foreach ($params as $key => $geometry) {
    if (in_array($key, $paramsToTest)) {
      if (is_numeric($geometry) && $geometry != 0) {
        try {
          civicrm_api3('Geometry', 'getSingle', ['id' => $geometry]);
        }
        catch (Exception $e) {
          throw new API_Exception("Geometrty #{$geometry} Does not exist in the database");
        }
      }
      elseif ($geometry != 0) {
        $test = CRM_Core_DAO::singleValueQuery("SELECT GeomFromText(%1)", [1 => [$geometry, 'String']]);
        if (empty($test)) {
          throw new API_Exception("Database cannot generate geometry from {$geometry}");
        }
      }
    }
  }
  $result = CRM_CiviGeometry_BAO_Geometry::contains($params);
  if (empty($result)) {
    return civicrm_api3_create_success(0);
  }
  elseif (is_array($result)) {
    return civicrm_api3_create_success($result);
  }
  else {
    return civicrm_api3_create_success(1);
  }
}

/**
 * Geometry.contains API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_contains_spec(&$spec) {
  $spec['geometry_a']['title'] = E::ts('Geometry A');
  $spec['geometry_a']['api.required'] = 1;
  $spec['geometry_a']['type'] = CRM_Utils_Type::T_INT;
  $spec['geometry_b']['title'] = E::ts('Geometry B');
  $spec['geometry_b']['api.required'] = 1;
}

/**
 * Geomety.unarchive
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_unarchive($params) {
  $archivedCheck = civicrm_api3('Geometry', 'get', ['id' => $params['id']]);
  if (empty($archivedCheck['values'][$archivedCheck['id']]['is_archived'])) {
    throw new \API_Exception("Geometry cannot be un archived if it is not archived");
  }
  $apiResult = [];
  $result = CRM_CiviGeometry_BAO_Geometry::unarchiveGeometry($params);
  _civicrm_api3_object_to_array($result, $apiResult[$result->id]); 
  return civicrm_api3_create_success($apiResult, $params);
}

/**
 * Geometry.unarchive API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_unarchive_spec(&$spec) {
  $spec['id']['title'] = E::ts('Geometry ID');
  $spec['id']['api.required'] = 1;
  $spec['id']['type'] = CRM_Utils_Type::T_INT;
}
