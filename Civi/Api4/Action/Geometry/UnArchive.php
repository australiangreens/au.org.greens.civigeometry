<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * UnArchive a Geometry Collection
 * @method getId()
 * @method setId(int $id)
 */
class UnArchive extends \Civi\Api4\Generic\AbstractAction {

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
    $archivedCheck = \Civi\Api4\Geometry::get(FALSE)->addWhere('id', '=', $params['id'])->execute();;
    if (empty($archivedCheck) || empty($archivedCheck[0]['is_archived'])) {
      throw new \API_Exception("Geometry cannot be un archived if it is not archived");
    }
    $archiveResult = \CRM_CiviGeometry_BAO_Geometry::unarchiveGeometry($params);
    $result[] = $archiveResult->toArray();
  }

}
