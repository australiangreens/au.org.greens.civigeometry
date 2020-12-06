<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;

/**
 * Create a GeometryEntity record
 * @method getEntityId()
 * @method setEntityId(int $id)
 * @method getEntityTable()
 * @method setEntityTable(string $entity_table)
 * @method getEntityId()
 * @method setEntityId(int $id)
 */
class CreateEntity extends \Civi\Api4\Generic\AbstractAction {

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
    if (empty($this->entity_id) || empty($this->entity_table) || empty($this->geometry_id)) {
      throw new \API_Exception('Must supply an entity_id, entity_table and a geometry_id');
    }
    $params = [
      'entity_id' => $this->entity_id,
      'entity_table' => $this->entity_table,
      'geometry_id' => $this->geometry_id,
    ];
    if (!empty($this->expiry_date)) {
      $params['expiry_date'] = $this->expiry_date;
    }
    \CRM_Utils_Hook::pre('create', 'GeometryEntity', NULL, $params);
    $instance = new \CRM_CiviGeometry_DAO_GeometryEntity();
    $instance->copyValues($params);
    $instance->save();
    \CRM_Utils_Hook::post('create', 'GeometryEntity', $instance->id, $instance);
    $result[] = $instance->toArray();
  }

}
