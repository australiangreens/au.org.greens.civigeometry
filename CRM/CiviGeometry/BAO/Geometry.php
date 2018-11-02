<?php
use CRM_CiviGeometry_ExtensionUtil as E;

class CRM_CiviGeometry_BAO_Geometry extends CRM_CiviGeometry_DAO_Geometry {

  /**
   * Create a new Geometry based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_CiviGeometry_DAO_Geometry|NULL
   */
  public static function create($params) {
    $className = 'CRM_CiviGeometry_DAO_Geometry';
    $entityName = 'Geometry';
    $hook = empty($params['id']) ? 'create' : 'edit';
    $geometry = FALSE;
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    if (!empty($params['id'])) {
      $instance->id = $params['id'];
      $instance->find();
    }
    if (!empty($params['geometry'])) {
      $geometry = $params['geometry'];
      unset($params['geometry']);
    }
    $instance->copyValues($params);
    $instance->save();
    if ($geometry) {
      CRM_Core_DAO::executeQuery("UPDATE civigeometry_geometry SET geometry = ST_GeomFromGeoJSON('{$geometry}') WHERE id = %1", [1 => [$instance->id, 'Positive']]);
    }
    $instance->geometry = CRM_Core_DAO::singleValueQuery("SELECT ST_asGeoJSON(geometry) FROM civigeometry_geometry WHERE id = %1", [1 => [$instance->id, 'Positive']]);
    if (!empty($params['collection_id'])) {
      civicrm_api3('Geometry', 'addCollection', [
        'geometry_id' => $instance->id,
        'collection_id' => $params['collection_id'],
      ]);
    }
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Add a Deometry to one or more collections. Note a Geometry must be part of a collection
   * @param array $params
   * @return array
   *   Values added
   */
  public static function addGeometryToCollection($params) {
    $count = 0;
    $result = [];
    foreach ($params['collection_id'] as $collection_id) {
      $gcg = new CRM_CiviGeometry_DAO_GeometryCollectionGeometry();
      $gcg->geometry_id = $params['geometry_id'];
      $gcg->collection_id = $collection_id;
      if ($gcg->find(TRUE)) {
        continue;
      }
      $gcg->save();
      _civicrm_api3_object_to_array($gcg, $result);
      $gcg->free();
      $count++;
    }
    $result['count'] = $count;
    return $result;
  }

}
