<?php

class CRM_CiviGeometry_Tasks {

  /**
   * Calculate the geometries that this address is within
   */
  public static function geoplaceAddress(CRM_Queue_TaskContext $ctx, $address_id) {
    try {
      $address = civicrm_api3('Address', 'get', ['id' => $address_id])['values'][$address_id];
    }
    catch (Exception $e) {
      Civi::log()->error('Address was not found in the database address_id {address_id}', ['address_id' => $address_id]);
      $address = FALSE;
    }
    if ($address) {
      civicrm_api3('Geometry', 'getentity', [
        'entity_id' => $address['id'],
        'entity_table' => 'civicrm_address',
        'api.Geometry.deleteentity' => [
          'entity_id' => "\$value.entity_id",
          'entity_table' => "\$value.entity_table",
          'geometry_id' => "\$value.geometry_id",
        ],
      ]);
      $geometry_ids = civicrm_api3('Geometry', 'contains', [
        'geometry_a' => 0,
        'geometry_b' => 'POINT(' . $address['geo_code_2'] . ' ' . $address['geo_code_1'] . ')',
      ])['values'];
      if (!empty($geometry_ids)) {
        foreach ($geometry_ids as $geometry_id) {
          civicrm_api3('Address', 'creategeometries', [
            'address_id' => $address['id'],
            'geometry_id' => $geometry_id,
          ]);
        }
      }
      $addressObject = new CRM_Core_BAO_Address();
      $addressObject->id = $address['id'];
      $addressObject->find(TRUE);
      // Trigger additional processing that might be needed following updates to the geoplacement of this address
      CRM_Utils_Hook::post('geoplace', 'Address', $address['id'], $addressObject);
    }
    return TRUE;
  }

  /**
   * Get all the Addresses for this geometry
   */
  public static function buildGeometryRelationships(CRM_Queue_TaskContext $ctx, $geometry_id) {
    foreach (getAddressesAsGenerator($params['geometry_id'], []) as $match) {
      civicrm_api3('Address', 'creategeometries', [
        'geometry_id' => $match['geometry_id'],
        'address_id' => $match['address_id'],
      ]);
    }
    return TRUE;
  }

}
