<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Get Overlaps between 2 Geometries.
 * @method getGeometryIdA()
 * @method setGeometryIdA($geometry_id_a)
 * @method getGeometryIdB()
 * @method setGeometryIdB($geometry_id_a)
 * @method getOverlap()
 * @method setOverlap(int $overlap)
 */
class GetOverlap extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Geometry id a
   *
   * @var mixed
   */
  protected $geometry_id_a;

  /**
   * Geometry Id b
   * @var mixed
   */
  protected $geometry_id_b;

  /**
   * Minimum overlap required.
   *
   * @var int
   */
  protected $overlap;

  public function _run(Result $result) {
    $params = [
      'geometry_id_a' => $this->geometry_id_a,
      'geometry_id_b' => $this->geometry_id_b,
      'overlap' => $this->overlap,
    ];
    $dbResult = \CRM_CiviGeometry_BAO_Geometry::calculateOverlapGeometry($params);
    if (!empty($dbResult)) {
      $result[] = $dbResult;
    }
  }

}
