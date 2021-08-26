<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Remove a Geoemtry from one or more Collections
 * @method getGeometryId()
 * @method setGeometryId(int $geometry_id)
 * @method getCollectionId()
 * @method setCollectionId(array $collection_id)
 */
class RemoveCollection extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Id of the Geometry
   *
   * @var int
   */
  protected $geometry_id;

  /**
   * Geometry Collection Ids
   *
   * @var array
   */
  protected $collection_id;

  public function _run(Result $result) {
    if (empty($this->geometry_id) || empty($this->collection_id)) {
      throw new \API_Exception('Must supply both a geometry_id and collection_id parameters');
    }
    if (!is_array($this->collection_id)) {
      throw new \API_Exception('Collection Id must be an Array');
    }
    $params = [
      'geometry_id' => $this->id,
      'collection_id' => $this->collection_id,
    ];
    \CRM_CiviGeometry_BAO_Geometry::removeGeometryFromCollection($params);
  }

}
