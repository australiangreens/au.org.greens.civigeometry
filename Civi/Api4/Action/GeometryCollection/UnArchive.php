<?php

namespace Civi\Api4\Action\GeometryCollection;

use Civi\Api4\Generic\Result;
use Civi\Api4\GeometryCollection;
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * UnArchive a GeometryCollection
 */
class UnArchive extends \Civi\Api4\Generic\AbstractAction {

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
    $currentStatus = GeometryCollection::get(FALSE)->addWhere('id', '=', $this->id)->execute();
    if (empty($currentStatus) || empty($currentStatus[0]['is_archived'])) {
      throw new \API_Exception(E::ts("Cannot unarchive a geometry collection that is not archived"));
    }
    $result[] = \CRM_CiviGeometry_BAO_GeometryCollection::unarchiveCollection($params)->toArray();
  }

}
