<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Geometry Contains Action
 * @method getGeometryA()
 * @method setGeometryA($geometry_a)
 * @method getGeometryB()
 * @method setGeometryB($geometry_b)
 * @method getGeometryACollectionId()
 * @method setGeometryACollectionId(int $collection_id)
 */
class Contains extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Geometry id a
   *
   * @var mixed
   */
  protected $geometry_a;

  /**
   * Geometry Id b
   * @var mixed
   */
  protected $geometry_b;

  /**
   * Geometry A Collection Id
   *
   * @var int
   */
  protected $geometry_a_collection_id;

  public function _run(Result $result) {
    $params = ['geometry_a' => $this->geometry_a, 'geometry_b' => $this->geometry_b];
    foreach ($params as $key => $geometry) {
      if (is_numeric($geometry) && $geometry != 0) {
        try {
          $geometry_test = \Civi\Api4\Geometry::get(FALSE)->addWhere('id', '=', $geometry)->execute();
          if (empty($geometry_test)) {
            throw new \API_Exception("Geometry #{$geometry} Does not exist in the database");
          }
        }
        catch (Exception $e) {
          throw new \API_Exception("Geometry #{$geometry} Does not exist in the database");
        }
      }
      elseif ($geometry != 0) {
        $test = \CRM_Core_DAO::singleValueQuery("SELECT GeomFromText(%1)", [1 => [$geometry, 'String']]);
        if (empty($test)) {
          throw new \API_Exception("Database cannot generate geometry from {$geometry}");
        }
      }
    }

    if ($params['geometry_a'] == 0) {
      // Wildcard. Find all the geometries that contain geometry_b
      // Result will be array of id strings, or empty array []
      $geomsContainingB = empty($this->geometry_a_collection_id)
        ? \CRM_CiviGeometry_BAO_Geometry::geometriesContaining($params['geometry_b'])
        : \CRM_CiviGeometry_BAO_Geometry::geometriesContaining($params['geometry_b'], $this->geometry_a_collection_id);

      if (!empty($geomsContainingB)) {
        foreach ($geomsContainingB as $geom) {
          $result[] = $geom;
        }
      }
      else {
        return;
      }
    }
    else {
      // Check if the geometry_a contains geometry_b
      $aContainsB = \CRM_CiviGeometry_BAO_Geometry::contains($params['geometry_a'], $params['geometry_b']);
      $result[] = $aContainsB ? 1 : 0;
    }
  }

}
