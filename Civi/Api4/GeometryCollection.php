<?php
namespace Civi\Api4;

/**
 * GeometryCollection entity.
 *
 * Provided by the CiviGeometry extension.
 *
 * @package Civi\Api4
 */
class GeometryCollection extends Generic\DAOEntity {

  /**
   * @param bool $checkPermissions
   * @return Action\GeometryCollection\Archive
   */
  public static function archive($checkPermissions = TRUE) {
    return (new Action\GeometryCollection\Archive(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\GeometryCollection\UnArchive
   */
  public static function unarchive($checkPermissions = TRUE) {
    return (new Action\GeometryCollection\UnArchive(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

}
