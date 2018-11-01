<?php
use CRM_CiviGeometry_ExtensionUtil as E;

class CRM_CiviGeometry_BAO_GeometryType extends CRM_CiviGeometry_DAO_GeometryType {

  /**
   * Create a new GeometryType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_CiviGeometry_DAO_GeometryType|NULL
   *
  public static function create($params) {
    $className = 'CRM_CiviGeometry_DAO_GeometryType';
    $entityName = 'GeometryType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
