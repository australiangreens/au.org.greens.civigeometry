<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Get Centroid of a Geometry
 * @method getId()
 * @method setId(int $id)
 */
class GetCentroid extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Id of the Geometry to archive
   *
   * @var int
   */
  protected $id;

  public function _run(Result $result) {
    if (empty($this->id)) {
      throw new \API_Exception('Must supply an ID');
    }
    $params = [
      'id' => $this->id,
    ];
    $result[] = \CRM_CiviGeometry_BAO_Geometry::getCentroid($params);
  }

}
