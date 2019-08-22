<?php

class CRM_CiviGeometry_BAO_GeometryCollection extends CRM_CiviGeometry_DAO_GeometryCollection {

  /**
   * Archive a Geometry Collection
   * @param array $params
   * @return CRM_CiviGeometry_DAO_GeometryCollection|NULL
   */
  public static function archiveCollection($params) {
    $instance = new CRM_CiviGeometry_DAO_GeometryCollection();
    $instance->id = $params['id'];
    $instance->find();
    $instance->is_archived = 1;
    $instance->archived_date = date('Ymdhis');
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
    $instance->is_archived = 0;
    $instance->save();
    CRM_Core_DAO::executeQuery("UPDATE civigeometry_geometry_collection SET archived_date = NULL WHERE id = %1", [1 => [$instance->id, 'Positive']]);
    $instance->find();
    CRM_Utils_Hook::post('unarchive', 'GeometryCollection', $instance->id, $instance);
    return $instance;
  }

}
