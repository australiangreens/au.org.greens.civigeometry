<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Delete a GeometryEntity record
 * @method getId()
 * @method setId(int $id)
 * @method getEntityId()
 * @method setEntityId(int $id)
 * @method getEntityTable()
 * @method setEntityTable(string $entity_table)
 * @method getEntityId()
 * @method setEntityId(int $id)
 */
class DeleteEntity extends \Civi\Api4\Generic\AbstractAction {

  /**
   * GeometryEntityID
   * @var int
   */
  protected $id;

  /**
   * Entity Id
   * @var int
   */
  protected $entity_id;

  /**
   * Entity Table
   * @var string
   */
  protected $entity_table;

  /**
   * Geometry ID
   * @var int
   */
  protected $geometry_id;

  /**
   * Optional Expiry Date
   * @var string
   */
  protected $expiry_date;

  public function _run(Result $result) {
    if (empty($this->id) && (empty($this->entity_id) || empty($this->entity_table) || empty($this->geometry_id))) {
      throw new \API_Exception('Must supply an entity_id, entity_table and a geometry_id if not supplying an id');
    }
    $params = [
      'id' => $this->id,
      'entity_id' => $this->entity_id,
      'entity_table' => $this->entity_table,
      'geometry_id' => $this->geometry_id,
    ];
    if (!empty($this->expiry_date)) {
      $params['expiry_date'] = $this->expiry_date;
    }
    \CRM_Utils_Hook::pre('delete', 'GeometryEntity', NULL, $params);
    $instance = new \CRM_CiviGeometry_DAO_GeometryEntity();
    $instance->copyValues($params);
    if ($instance->find()) {
      while ($instance->fetch()) {
        $instance->delete();
        \CRM_Utils_Hook::post('delete', 'GeometryEntity', $instance->id, $instance);
        $result[] = $instance->toArray();
      }
    }
    else {
      throw new \API_Exception('Could not delete entity geometry relationship with params ' . json_encode($params));
    }
  }

}
