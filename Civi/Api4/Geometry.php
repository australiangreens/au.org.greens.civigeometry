<?php
namespace Civi\Api4;

/**
 * Geometry entity.
 *
 * Provided by the CiviGeometry extension.
 *
 * @package Civi\Api4
 */
class Geometry extends Generic\DAOEntity {

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\Create
   */
  public static function create($checkPermissions = TRUE) {
    return (new Action\Geometry\Create(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\Update
   */
  public static function update($checkPermissions = TRUE) {
    return (new Action\Geometry\Update(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\Get
   */
  public static function get($checkPermissions = TRUE) {
    return (new Action\Geometry\Get(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\GeometryCollection\GetCollection
   */
  public static function getcollection($checkPermissions = TRUE) {
    return (new Action\Geometry\GetCollection(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\GeometryCollection\AddCollection
   */
  public static function addcollection($checkPermissions = TRUE) {
    return (new Action\Geometry\AddCollection(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\GeometryCollection\RemoveCollection
   */
  public static function removecollection($checkPermissions = TRUE) {
    return (new Action\Geometry\RemoveCollection(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\Archive
   */
  public static function archive($checkPermissions = TRUE) {
    return (new Action\Geometry\Archive(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\UnArchive
   */
  public static function unarchive($checkPermissions = TRUE) {
    return (new Action\Geometry\UnArchive(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\GetCentroid
   */
  public static function getcentroid($checkPermissions = TRUE) {
    return (new Action\Geometry\GetCentroid(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\Contains
   */
  public static function contains($checkPermissions = TRUE) {
    return (new Action\Geometry\Contains(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\GetDistance
   */
  public static function getdistance($checkPermissions = TRUE) {
    return (new Action\Geometry\GetDistance(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\GetOverlap
   */
  public static function getoverlap($checkPermissions = TRUE) {
    return (new Action\Geometry\GetOverlap(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\GetBounds
   */
  public static function getbounds($checkPermissions = TRUE) {
    return (new Action\Geometry\GetBounds(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\GetSpatialData
   */
  public static function getspatialdata($checkPermissions = TRUE) {
    return (new Action\Geometry\GetSpatialData(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\CreateEntity
   */
  public static function createentity($checkPermissions = TRUE) {
    return (new Action\Geometry\CreateEntity(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\DeleteEntity
   */
  public static function deleteentity($checkPermissions = TRUE) {
    return (new Action\Geometry\DeleteEntity(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\GetEntity
   */
  public static function getentity($checkPermissions = TRUE) {
    return (new Action\Geometry\GetEntity(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\GetIntersection
   */
  public static function getintersection($checkPermissions = TRUE) {
    return (new Action\Geometry\GetIntersection(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * @param bool $checkPermissions
   * @return Action\Geometry\GetNearest
   */
  public static function getnearest($checkPermissions = TRUE) {
    return (new Action\Geometry\GetNearest(__CLASS__, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

}
