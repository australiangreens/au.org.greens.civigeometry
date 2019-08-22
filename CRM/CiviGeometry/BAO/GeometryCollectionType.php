<?php

class CRM_CiviGeometry_BAO_GeometryCollectionType extends CRM_CiviGeometry_DAO_GeometryCollectionType {

  /**
   * Create a new GeometryCollectionType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_CiviGeometry_DAO_GeometryCollectionType|NULL
   */
  public static function create($params) {
    $className = 'CRM_CiviGeometry_DAO_GeometryCollectionType';
    $entityName = 'GeometryCollectionType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

}
