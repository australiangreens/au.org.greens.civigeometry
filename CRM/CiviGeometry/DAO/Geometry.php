<?php

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id 
 * @property string $geometry_type_id 
 * @property string $label 
 * @property string $description 
 * @property bool|string $is_archived 
 * @property string $archived_date 
 * @property string $geometry 
 */
class CRM_CiviGeometry_DAO_Geometry extends CRM_CiviGeometry_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'civigeometry_geometry';

}
