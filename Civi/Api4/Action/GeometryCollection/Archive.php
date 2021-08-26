<?php

namespace Civi\Api4\Action\GeometryCollection;

use Civi\Api4\Generic\Result;

/**
 * Archive a Geometry Collection
 */
class Archive extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Id of the Geometry Collection to archive
   *
   * @var int
   */
  protected $id;

  public function _run(Result $result) {
    $params = [
      'id' => $this->id,
    ];
    $archiveResult = \CRM_CiviGeometry_BAO_GeometryCollection::archiveCollection($params);
    $result[] = $archiveResult->toArray();
  }

}
