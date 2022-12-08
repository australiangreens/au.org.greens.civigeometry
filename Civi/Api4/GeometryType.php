<?php
namespace Civi\Api4;

/**
 * GeometryType entity.
 *
 * Provided by the CiviGeometry extension.
 *
 * @package Civi\Api4
 */
class GeometryType extends Generic\DAOEntity {

  public static function permissions() {
    return [
      'create' => [['administer geometry', 'administer civicrm']],
      'delete' => [['administer geometry', 'administer civicrm']],
      'default' => ['access geometry'],
    ];
  }

}
