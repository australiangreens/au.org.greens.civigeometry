<?php

class CRM_CiviGeometry_Tasks {

  /**
   * Calculate the geometries that this address is within
   */
  public static function geoplaceAddress(CRM_Queue_TaskContext $ctx, $address_id) {
    CRM_Core_DAO::executeQuery("DELETE FROM civigeometry_address_geometry WHERE address_id = %1", [
      1 => [$address['id'], 'Positive'],
    ]);
    $geometry_ids = civicrm_api3('Geometry', 'contains', [
      'geometry_a' => 0,
      'geometry_b' => 'POINT(' . $address['geo_code_2'] . ' ' . $address['geo_code_1'] . ')',
    ])['values'];
    if (!empty($geometry_ids)) {
      foreach ($geometry_ids as $geometry_id) {
        civicrm_api3('Address', 'creategeometries', [
          'address_id' => $id,
          'geometry_id' => $geometry_id,
        ]);
      }
    }
    return TRUE;
  }

  /**
   * Get all the Addresses for this geometry
   */
  public static function buildGeometryRelationships(CRM_Queue_TaskContext $ctx, $geometry_id) {
    $matches = CRM_CiviGeometry_BAO_Geometry::getAddresses($geometry_id);
    foreach ($matches as $match) {
      civicrm_api3('Address', 'creategeometries', [
        'geometry_id' => $match['geometry_id'],
        'address_id' => $match['address_id'],
      ]);
    }
    return TRUE;
  }

}
