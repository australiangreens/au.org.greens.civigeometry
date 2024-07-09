<?php

class CRM_CiviGeometry_Utils {

  /**
   * Callback function for the dynamic foreign key
   * definition for the GeometryEntity entity.
   * Referenced in xml/schema/CRM/CiviGeometry/GeometryEntity.xml
   */
  public static function getSupportedEntities(): array {
    return [
      'civicrm_contact' => ts("Contact"),
      'civicrm_address' => ts("Address"),
    ];
  }
}

