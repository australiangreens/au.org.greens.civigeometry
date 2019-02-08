<?php
use CRM_CiviGeometry_ExtensionUtil as E;

class CRM_CiviGeometry_BAO_GeometryCollection extends CRM_CiviGeometry_DAO_GeometryCollection {

  /**
   * Create a new GeometryCollection based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_CiviGeometry_DAO_GeometryCollection|NULL
   *
  public static function create($params) {
    $className = 'CRM_CiviGeometry_DAO_GeometryCollection';
    $entityName = 'GeometryCollection';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

  /**
   * Archive a Geometry Collection
   * @param array $params
   * @return CRM_CiviGeometry_DAO_GeometryCollection|NULL
   */
  public static function archiveCollection($params) {
    $instance = new CRM_CiviGeometry_DAO_GeometryCollection();
    $instance->id = $params['id'];
    $instance->find();
    $instance->is_archive = 1;
    $instance->archive_date = date('Ymdhis');
    $instance->save();
    $instance->find();
    CRM_Utils_Hook::post('archive', 'GeometryCollection', $instance->id, $instance);
    return $instance;
  }

  /**
   * Archive a Geometry Collection
   * @param array $params
   * @return CRM_CiviGeometry_DAO_GeometryCollection|NULL
   */
  public static function unarchiveCollection($params) {
    $instance = new CRM_CiviGeometry_DAO_GeometryCollection();
    $instance->id = $params['id'];
    $instance->find();
    $instance->is_archive = 0;
    $instance->save();
    CRM_Core_DAO::executeQuery("UPDATE civigeometry_geometry_collection SET archive_date = NULL WHERE id = %1", [1 => [$instance->id, 'Positive']]);
    $instance->find();
    CRM_Utils_Hook::post('unarchive', 'GeometryCollection', $instance->id, $instance);
    return $instance;
  }

}
