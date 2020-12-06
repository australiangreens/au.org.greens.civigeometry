<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Archive a Geometry Collection
 * @method getId()
 * @method setId(int $id)
 */
class Archive extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Id of the Geometry to archive
   *
   * @var int
   */
  protected $id;

  public function _run(Result $result) {
    $params = [
      'id' => $this->id,
    ];
    $archiveResult = \CRM_CiviGeometry_BAO_Geometry::archiveGeometry($params);
    $result[] = $archiveResult->toArray();
  }

}
