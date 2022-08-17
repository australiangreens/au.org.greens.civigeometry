<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Add A Geometry to one or more collections
 * @method getGeometryA()
 * @method setGeometryA(string $geometry_a)
 * @method getGeometryB()
 * @method setGeometryB(string $geometry_b)
 */
class GetDistance extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Geometry id a
   *
   * @var string
   */
  protected $geometry_a;

  /**
   * Geometry Id b
   * @var string
   */
  protected $geometry_b;

  public function _run(Result $result) {
    $params = [
      'geometry_a' => $this->geometry_a,
      'geometry_b' => $this->geometry_b,
    ];
    $result[] = \CRM_CiviGeometry_BAO_Geometry::calculateDistance($params);
  }

}
