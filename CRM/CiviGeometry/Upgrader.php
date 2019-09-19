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
    $this->ctx->log->info('Applying update 4201 - Adding in AddressGeometry Table');
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry DROP INDEX index_geometry_type_label");
    CRM_Core_DAO::executeQuery("ALTER TABLE civigeometry_geometry ADD INDEX index_geometry_type_label_is_archived (`label`, `geometry_type_id`, `is_archived`)");
    return TRUE;
  }

  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
   */
  //public function upgrade_4201() {
  //  $this->ctx->log->info('Applying update 4201');
  //  // this path is relative to the extension base dir
  //  $this->executeSqlFile('sql/upgrade_4201.sql');
  //  return TRUE;
  //}


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
   */
  //public function upgrade_4202() {
  //  $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

  //  $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //  $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //  $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //  return TRUE;
  //}
  //public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  //public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  //public function processPart3($arg5) { sleep(10); return TRUE; }
  //


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
   */
  //public function upgrade_4203() {
  //  $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

  //  $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //  $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //  for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //    $endId = $startId + self::BATCH_SIZE - 1;
  //    $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //      1 => $startId,
  //      2 => $endId,
  //    ));
  //    $sql = '
  //      UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
  //      WHERE id BETWEEN %1 and %2
  //    ';
  //    $params = array(
  //      1 => array($startId, 'Integer'),
  //      2 => array($endId, 'Integer'),
  //    );
  //    $this->addTask($title, 'executeSql', $sql, $params);
  //  }
  //  return TRUE;
  //}

}
