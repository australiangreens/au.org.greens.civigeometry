<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Get Overlaps between 2 Geometries.
 * @method getGeometryIdA()
 * @method setGeometryIdA($geometry_id)
 * @method getOverlap()
 * @method setOverlap(int $overlap)
 */
class GetCachedOverlaps extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Geometry id a
   *
   * @var int
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
      'geometry_id' => $this->geometry_id_a,
      'overlap' => $this->overlap,
    ];
    return \CRM_CiviGeometry_BAO_Geometry::getCachedOverlappingGeometries($params);
  }

}
