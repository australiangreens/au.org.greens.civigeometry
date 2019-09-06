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
        catch (Exception $e) {
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
 * Geometry.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_get_spec(&$spec) {
  $spec['format'] = [
    'title' => E::ts('Geometry OutputFormat'),
    'type' => CRM_Utils_Type::T_STRING,
  ];
  $spec['collection_id'] = [
    'title' => E::ts('Geometry Collection ID'),
  ];
}

/**
 * Geometry.get API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_get($params) {
  if (!empty($params['format']) && !in_array($params['format'], ['json', 'kml', 'wkt'])) {
    throw new API_Exception(E::ts('Output format must be one of json, kml or wkt'));
  }
  $sql = NULL;
  if (!empty($params['collection_id'])) {
    $geometries = civicrm_api3('Geometry', 'getcollection', [
      'collection_id' => $params['collection_id'],
      'return' => ['geometry_id'],
      'options' => ['limit' => 0],
    ]);
    $geometryIds = CRM_Utils_Array::collect('geometry_id', $geometries['values']);
    $sql = CRM_Utils_SQL_Select::fragment()->where('id IN (#geometryIDs)', ['geometryIDs' => $geometryIds]);
  }
  // Note we append additional SQL where clause here if collection_id is specified, this is a pseudo field
  $results = _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params, TRUE, "", $sql);
  if (!empty($results['values'])) {
    foreach ($results['values'] as $key => $values) {
      // Geometry field was returned in the select so we need to re-format into geoJSON, kml or wkt format.
      if (isset($values['geometry'])) {
        $id = !empty($params['sequential']) ? $values['id'] : $key;
        $mySQLFunction = 'ST_AsGeoJSON';
        $kml = FALSE;
        // If we are outputting a KML or wkt format then we use ST_asText rather than ST_AsGeoJSON
        if (!empty($params['format']) && in_array($params['format'], ['kml', 'wkt'])) {
          $mySQLFunction = 'ST_AsText';
          if ($params['format'] == 'kml') {
            $kml = TRUE;
          }
        }
        $geometry = CRM_Core_DAO::singleValueQuery("SELECT {$mySQLFunction}(geometry) FROM civigeometry_geometry WHERE id = %1", [1 => [$id, 'Positive']]);
        if ($kml) {
          $geometry = CRM_CiviGeometry_BAO_Geometry::wkt2kml($geometry);
        }
        $results['values'][$key]['geometry'] = $geometry;
      }
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
  $spec['geometry_id']['title'] = E::ts('Geometry ID');
  $spec['geometry_id']['type'] = CRM_Utils_Type::T_INT;
  $spec['collection_id']['title'] = E::ts('Collection IDs');
  $spec['collection_id']['type'] = CRM_Utils_Type::T_INT;
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
  $spec['geometry_a_collection_id']['title'] = E::ts('Geometry a Collection ID');
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
 * Geomety.getdistance
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_getdistance($params) {
  $result = CRM_CiviGeometry_BAO_Geometry::calculateDistance($params);
  return civicrm_api3_create_success($result, $params);
}

/**
 * Geometry.getdistance API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_getdistance_spec(&$spec) {
  $spec['geometry_a']['title'] = E::ts('Geometry A');
  $spec['geometry_a']['api.required'] = 1;
  $spec['geometry_a']['type'] = CRM_Utils_Type::T_STRING;
  $spec['geometry_b']['title'] = E::ts('Geometry B');
  $spec['geometry_b']['api.required'] = 1;
  $spec['geometry_b']['type'] = CRM_Utils_Type::T_STRING;
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

/**
 * Geomety.getOverlap
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_geometry_getoverlap($params) {
  $result = [];
  $overlapResult = CRM_CiviGeometry_BAO_Geometry::calculateOverlapGeometry($params);
  $result[$overlapResult['id']] = $overlapResult;
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
function _civicrm_api3_geometry_getoverlap_spec(&$spec) {
  $spec['geometry_id_a']['title'] = E::ts('Geometry ID A');
  $spec['geometry_id_a']['api.required'] = 1;
  $spec['geometry_id_a']['type'] = CRM_Utils_Type::T_INT;
  $spec['geometry_id_b']['title'] = E::ts('Geometry ID B');
  $spec['geometry_id_b']['api.required'] = 1;
  $spec['geometry_id_b']['type'] = CRM_Utils_Type::T_INT;
}

/**
 * Geometry.getspatialdata API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_getspatialdata_spec(&$spec) {
  $spec['id'] = [
    'title' => E::ts('Geometry ID'),
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];
}

/**
 * Return Spatial information about a perticular geometry
 * @param array $params
 * @return array
 */
function civicrm_api3_geometry_getspatialdata($params) {
  $apiResult = [];
  $apiResult[$params['id']] = CRM_CiviGeometry_BAO_Geometry::returnSpatialInformation($params['id']);
  return civicrm_api3_create_success($apiResult);
}

/**
 * Geometry.getbounds API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_getbounds_spec(&$spec) {
  $spec['id'] = [
    'title' => E::ts('Geometry ID'),
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
  ];
}

/**
 * Return the bounds of the geometry
 * @param array $params
 * @return array
 */
function civicrm_api3_geometry_getbounds($params) {
  $apiResult = [];
  $apiResult[$params['id']] = CRM_CiviGeometry_BAO_Geometry::generateBounds($params['id']);
  return civicrm_api3_create_success($apiResult);
}

/**
 * Geometry.getbounds API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_getaddressgeometry_spec(&$spec) {
  $spec['geometry_id'] = [
    'title' => E::ts('Geometry ID'),
    'type' => CRM_Utils_Type::T_INT,
    'FKApiName' => 'Geometry',
    'FKClassName' => 'CRM_CiviGeometry_BAO_Geometry',
    'FKKeyColumn' => 'id',
  ];
  $spec['address_id'] = [
    'title' => E::ts('Address ID'),
    'type' => CRM_Utils_Type::T_INT,
    'FKApiName' => 'Address',
    'FKClassName' => 'CRM_Core_BAO_Address',
    'FKKeyColumn' => 'id',
  ];
  $spec['is_active'] = [
    'title' => E::ts('Limit to only Active Geometries'),
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.default' => 1,
  ];
}

/**
 * Return the bounds of the geometry
 * @param array $params
 * @return array
 */
function civicrm_api3_geometry_getaddressgeometry($params) {
  $sql = '';
  if (empty($params['geometry_id']) && empty($params['address_id'])) {
    throw new \API_Exception(E::ts('Must supply one of geometry_id or address_id'));
  }
  if ($params['is_active']) {
    $sql = CRM_Utils_SQL_Select::fragment()->where('a.geometry_id IN (SELECT id FROM civigeometry_geometry WHERE is_archived = 0)');
  }
  return _civicrm_api3_basic_get('CRM_CiviGeometry_BAO_AddressGeometry', $params, TRUE, "", $sql);
}

/**
 * Geometry.getbounds API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_geometry_createaddressgeometry_spec(&$spec) {
  $spec['geometry_id'] = [
    'title' => E::ts('Geometry ID'),
    'type' => CRM_Utils_Type::T_INT,
    'FKApiName' => 'Geometry',
    'FKClassName' => 'CRM_CiviGeometry_BAO_Geometry',
    'FKKeyColumn' => 'id',
  ];
  $spec['address_id'] = [
    'title' => E::ts('Address ID'),
    'type' => CRM_Utils_Type::T_INT,
    'FKApiName' => 'Address',
    'FKClassName' => 'CRM_Core_BAO_Address',
    'FKKeyColumn' => 'id',
  ];
}

/**
 * Return the bounds of the geometry
 * @param array $params
 * @return array
 */
function civicrm_api3_geometry_createaddressgeometry($params) {
  return _civicrm_api3_basic_create('CRM_CiviGeometry_BAO_AddressGeometry', $params);
}
