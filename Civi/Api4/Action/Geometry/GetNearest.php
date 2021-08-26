<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Get the Nearest Geometries
 * @method getPoint()
 * @method setPoint(string $point)
 * @method getDistance()
 * @method setDistance(int $distance)
 * @method getCollectionId()
 * @method setCollectionId(int $collection_id)
 * @method getGeometryId()
 * @method setGeometryId(int|array $geometry_id)
 */
class GetNearest extends \Civi\Api4\Generic\AbstractAction {

  /**
   * A String representing the starting point for distance
   *
   * @var string
   */
  protected $point;

  /**
   * Maximum distance in KM between center of the geometry and point
   *
   * @var int
   */
  protected $distance;

  /**
   * Geometry Collection ID
   *
   * @var int
   */
  protected $collection_id;

  /**
   * Geometry ID
   *
   * @var int|array
   */
  protected $geometry_id;

  public function _run(Result $result) {
    $params = [
      'point' => $this->point,
      'distance' => $this->distance,
      'collection_id' => $this->collection_id,
      'geometry_id' => $this->geometry_id,
    ];
    $results = \CRM_CiviGeometry_BAO_Geometry::getNearestGeometries($params);
    foreach ($results as $res) {
      $result[] = $res;
    }

  }

}
