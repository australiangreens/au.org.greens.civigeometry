<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Get The intersection between 2 geometries.
 * @method getGeometryA()
 * @method setGeometryA(int $geometry_a)
 * @method getGeometryB()
 * @method setGeometryB(int $geometry_b)
 * @method getCollectionId()
 * @method setCollectionId(int $collection_id)
 */
class GetIntersection extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Geometry id a
   *
   * @var int
   */
  protected $geometry_a;

  /**
   * Geometry Id b
   * @var int
   */
  protected $geoemtry_b;

  /**
   * Geometry B/A Collection ID
   *
   * @var int
   */
  protected $collection_id;

  public function _run(Result $result) {
    // Ensure that we have at least geometry_a or geometry_b
    if (empty($this->geometry_a) && empty($this->geometry_b)) {
      throw new \API_Exception('Must supply either geometry_a or geometry_b');
    }
    if (!empty($this->geometry_a)) {
      $test = 'b';
    }
    else {
      $test = 'a';
    }
    // Check that we have either the other geometry or a collection id.
    if (empty($this->geometry_{$test}) && empty($this->collection_id)) {
      throw new \API_Exception('Must supply either geometry_' . $test . ' OR collection_id');
    }
    $params = [
      'geometry_a' => $this->geometry_a,
      'geometry_b' => $this->geometry_b,
      'collection_id' => $this->collection_id,
    ];
    $result[] = \CRM_CiviGeometry_BAO_Geometry::getGeometryIntersection($params);
  }

}
