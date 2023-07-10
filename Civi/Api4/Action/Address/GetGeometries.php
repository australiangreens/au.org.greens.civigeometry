<?php

namespace Civi\Api4\Action\Address;

use Civi\Api4\Generic\Result;

/**
 * Address GetGeometries action
 * @method getGeometries()
 */
class GetGeometries extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Address Id
   * @var int
   * @required
   */
  protected $address_id;

  /**
   * Geometry Id
   * @var int
   */
  protected $geometry_id;

  /**
   * Skip cache
   * @var bool
   */
  protected $skip_cache;

  public function _run(Result $result) {
    if (!empty($this->skip_cache)) {
      $address = \Civi\Api4\Address::get(FALSE)
        ->addSelect('id', 'geo_code_1', 'geo_code_2')
        ->addWhere('id', '=', $this->address_id)
        ->setLimit(1)
        ->execute()
        ->first();
      if (!empty($address['geo_code_1']) && !empty($address['geo_code_2'])) {
        $addressGeoJson = 'POINT(' . $address['geo_code_2'] . ' ' . $address['geo_code_1'] . ')';
        $geometries = \Civi\Api4\Geometry::contains(FALSE)
          ->addWhere('geometry_a', '=', 0)
          ->addWhere('geometry_b', '=', $addressGeoJson)
          ->execute();
        foreach ($geometries as $geometry) {
          $result[] = [
            'address_id' => $this->address_id,
            'geometry_id' => $geometry->id,
          ];
        }
      }
    }
    else {
      $geometries = \Civi\Api4\Geometry::getEntity(FALSE)
        ->addSelect('geometry_id')
        ->addWhere('entity_table', '=', 'civicrm_address')
        ->addWhere('entity_id', '=', $this->address_id)
        ->execute();
      while ($geometries->fetch()) {
        $result[] = [
          'id' => $geometries->id,
          'entity_id' => $geometries->entity_id,
          'entity_table' =>'civicrm_address',
          'geometry_id' => $geometries->geometry_id,
        ];
      }
    }
  }
}
