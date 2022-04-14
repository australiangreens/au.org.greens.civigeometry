<?php

/**
 * Collection of upgrade steps.
 */
class CRM_CiviGeometry_Upgrader extends CRM_CiviGeometry_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   */
  public function install() {
    $this->executeSql("DROP FUNCTION IF EXISTS earth_circle_distance");
    $this->executeSql("
CREATE FUNCTION earth_circle_distance(point1 point, point2 point) RETURNS double
    DETERMINISTIC
begin
  declare lon1, lon2 double;
  declare lat1, lat2 double;
  declare td double;
  declare d_lat double;
  declare d_lon double;
  declare a, c, R double;

  set lon1 = X(GeomFromText(AsText(point1)));
  set lon2 = X(GeomFromText(AsText(point2)));
  set lat1 = Y(GeomFromText(AsText(point1)));
  set lat2 = Y(GeomFromText(AsText(point2)));

  set d_lat = radians(lat2 - lat1);
  set d_lon = radians(lon2 - lon1);

  set lat1 = radians(lat1);
  set lat2 = radians(lat2);

  set R = 6372.8; -- in kilometers

  set a = sin(d_lat / 2.0) * sin(d_lat / 2.0) + sin(d_lon / 2.0) * sin(d_lon / 2.0) * cos(lat1) * cos(lat2);
  set c = 2 * asin(sqrt(a));

  return R * c;
end
");
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  public function postInstall() {
    CRM_Core_DAO::executeQuery('ALTER TABLE civigeometry_geometry ADD SPATIAL INDEX(`geometry`)');
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   */
  public function uninstall() {
    $this->executeSqlFile('sql/uninstall_geometry_calcuation_function.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  //public function enable() {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  //}

  /**
   * Example: Run a simple query when a module is disabled.
   */
  //public function disable() {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  //}

  /**
   * Add Spatial Data Index to civigeometry_geoemtry table.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200 - Applying Spatial Index to civigeometry_geometry');
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry CHANGE geometry geometry geometry  NOT NULL  COMMENT 'The Spatial data for this geometry'");
    CRM_Core_DAO::executeQuery('ALTER TABLE civigeometry_geometry ADD SPATIAL INDEX(`geometry`)');
    return TRUE;
  }

  /**
   * Add in AddressGeometry Table
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201 - Adding in AddressGeometry Table');
    $this->executeSqlFile('sql/address_geometry.sql');
    return TRUE;
  }

  /**
   * Alter index on civigeoemtry
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4202() {
    $this->ctx->log->info('Applying update 4202 - Alter index on civigeometry_geometry to include is_archived column and not be unique');
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry DROP CONSTRAINT FK_civigeometry_geometry_geometry_type_id");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry DROP INDEX index_geometry_type_label");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry ADD INDEX index_geometry_type_label_is_archived (`label`, `geometry_type_id`, `is_archived`)");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry ADD CONSTRAINT FK_civigeometry_geometry_geometry_type_id FOREIGN KEY (`geometry_type_id`) REFERENCES `civigeometry_geometry_type`(`id`) ON DELETE CASCADE");
    return TRUE;
  }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4203() {
    $this->ctx->log->info('Applying update 4203 - Rename civigeometry_address_geometry table as civigeometry_geometry_entity and add in entity_table column and rename address_id as entity_id');
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_address_geometry RENAME civigeometry_geometry_entity");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity DROP CONSTRAINT FK_civigeometry_address_geometry_address_id");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity DROP CONSTRAINT FK_civigeometry_address_geometry_geometry_id");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity DROP INDEX UI_geometry_id_address_id");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity CHANGE address_id entity_id int unsigned NOT NULL COMMENT 'entity id that is associated with this geometry'");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity ADD COLUMN entity_table varchar(255) NOT NULL COMMENT 'entity table that is associated with this geometry'");
    CRM_Core_DAO::executeQuery("UPDATE civigeometry_geometry_entity SET entity_table = 'civicrm_address'");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity ADD UNIQUE INDEX UI_geometry_id_entity_id_entity_table(geometry_id,entity_id,entity_table)");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity ADD CONSTRAINT FK_civigeometry_geometry_entity_geometry_id FOREIGN KEY (`geometry_id`) REFERENCES `civigeometry_geometry`(`id`) ON DELETE CASCADE");
    return TRUE;
  }

  /**
   * Add in expiry date column onto the geometry_entity table
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4204() {
    $this->ctx->log->info('Applying update 4204 - Add in expiry_date column onto the geometry entity table');
    $this->executeSqlFile('sql/geometry_entity_expiry_date.sql');
    return TRUE;
  }

  /**
   * Modify existing indexes to apply consistent naming
   * and improve composite index performance
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_4205() {
    $this->ctx->log->info('Applying update 4205 - Refactor indexes on several tables');
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity DROP FOREIGN KEY FK_civigeometry_geometry_entity_geometry_id");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity DROP INDEX UI_geometry_id_entity_id_entity_table");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity ADD UNIQUE INDEX index_entity_table_geometry_id_entity_id(entity_table,geometry_id,entity_id)");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity ADD FOREIGN KEY FK_civigeometry_geometry_entity_geometry_id (geometry_id) REFERENCES civigeometry_geometry (id) ON DELETE CASCADE");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_collection_type DROP INDEX UI_label");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_collection_type ADD UNIQUE INDEX index_label(label)");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry DROP INDEX index_geometry_type_label_is_archived");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry ADD INDEX index_is_archived_geometry_type_label(is_archived,geometry_type_id,label)");
    return TRUE;
  }

  /**
   * Add index to geometry_entity table
   * (using update number 5184 to reflect Civi major version and extension major/minor/patch)
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_5184() {
    $this->ctx->log->info('Applying update 5184 - Adding index on civigeometry_geometry_entity table');
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry_entity ADD INDEX index_entity_table_entity_id(entity_table,entity_id)");
    return TRUE;
  }

}

