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
      // Remove all existing geometry relationships for this address
      CRM_Core_DAO::executeQuery("
        DELETE FROM civigeometry_geometry_entity
        WHERE entity_id = %1 AND entity_table = 'civicrm_address'
      ", [
        1 => [$address['id'], 'Positive'],
      ]);
      // Find all containing geometries, then insert relationships.
      // Separated into SELECT + INSERT to avoid shared locks on civigeometry_geometry.
      $point = 'POINT(' . $address['geo_code_2'] . ' ' . $address['geo_code_1'] . ')';
      $dao = CRM_Core_DAO::executeQuery("
        SELECT SQL_NO_CACHE g.id
        FROM civigeometry_geometry g
        WHERE g.is_archived = 0
          AND ST_Contains(g.geometry, ST_GeomFromText(%1, 4326))
      ", [
        1 => [$point, 'String'],
      ]);
      $geometryIds = array_column($dao->fetchAll(), 'id');
      if ($geometryIds) {
        $addressId = (int) $address['id'];
        $values = [];
        foreach ($geometryIds as $geometryId) {
          $values[] = '(' . $addressId . ", 'civicrm_address', " . (int) $geometryId . ')';
        }
        CRM_Core_DAO::executeQuery(
          "INSERT IGNORE INTO civigeometry_geometry_entity (entity_id, entity_table, geometry_id) VALUES " . implode(',', $values)
        );
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
    CRM_CiviGeometry_BAO_Geometry::buildAddressRelationshipsBatch($geometry_id);
    return TRUE;
  }

}
