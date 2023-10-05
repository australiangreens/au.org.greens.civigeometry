<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Get Overlaps between 2 Geometries.
 * @method getGeometryId()
 * @method setGeometryId(int|array $geometry_id)
 * @method getOverlap()
 * @method setOverlap(int $overlap)
 */
class GetCachedOverlaps extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Geometry id a
   *
   * @var int|array
   * @required
   */
  protected $geometry_id;

  /**
   * Minimum overlap required.
   *
   * @var int
   */
  protected $overlap;

  public function _run(Result $result) {
    $params = [
      'geometry_id' => $this->geometry_id,
      'overlap' => $this->overlap,
    ];
    // If we've been passed an array of geometry IDs, we need to transform
    // the geometry_id value to be a nested array of the shape
    // "IN" => [geometry_id, geometry_id, ...]
    if (is_array($params['geometry_id'])) {
      $params['geometry_id'] = ['IN' => $params['geometry_id']];
    }
    $dbResult = \CRM_CiviGeometry_BAO_Geometry::getCachedOverlappingGeometries($params);
    foreach ($dbResult as $res) {
      $result[] = $res;
    }
  }

}
