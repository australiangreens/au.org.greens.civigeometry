<?php
namespace Civi\Api4;

/**
 * GeometryEntity entity.
 *
 * Provided by the CiviGeometry extension.
 *
 * @package Civi\Api4
 */
class GeometryEntity extends Generic\DAOEntity {

  public static function permissions() {
    return [
      'default' => ['access geometry'],
      'create' => [['administer geometry', 'administer civicrm']],
      'delete' => [['administer geometry', 'administer civicrm']],
    ];
  }
}
