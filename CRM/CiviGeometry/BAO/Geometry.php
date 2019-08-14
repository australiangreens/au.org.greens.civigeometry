<?php
use CRM_CiviGeometry_ExtensionUtil as E;

class CRM_CiviGeometry_BAO_Geometry extends CRM_CiviGeometry_DAO_Geometry {

  /**
   * Create a new Geometry based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_CiviGeometry_DAO_Geometry|NULL
   */
  public static function create($params) {
    $className = 'CRM_CiviGeometry_DAO_Geometry';
    $entityName = 'Geometry';
    $hook = empty($params['id']) ? 'create' : 'edit';
    $geometry = FALSE;
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    if (!empty($params['id'])) {
      $instance->id = $params['id'];
      $instance->find();
    }
    if (!empty($params['geometry'])) {
      $geometry = $params['geometry'];
      unset($params['geometry']);
    }
    $instance->copyValues($params);
    $instance->save();
    if ($geometry) {
      CRM_Core_DAO::executeQuery("UPDATE civigeometry_geometry SET geometry = ST_GeomFromGeoJSON('{$geometry}') WHERE id = %1", [1 => [$instance->id, 'Positive']]);
    }
    $instance->geometry = CRM_Core_DAO::singleValueQuery("SELECT ST_asGeoJSON(geometry) FROM civigeometry_geometry WHERE id = %1", [1 => [$instance->id, 'Positive']]);
    if (!empty($params['collection_id'])) {
      civicrm_api3('Geometry', 'addCollection', [
        'geometry_id' => $instance->id,
        'collection_id' => $params['collection_id'],
      ]);
    }
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Add a Deometry to one or more collections. Note a Geometry must be part of a collection
   * @param array $params
   * @return array
   *   Values added
   */
  public static function addGeometryToCollection($params) {
    $result = [];
    foreach ($params['collection_id'] as $collection_id) {
      $gcg = new CRM_CiviGeometry_DAO_GeometryCollectionGeometry();
      $gcg->geometry_id = $params['geometry_id'];
      $gcg->collection_id = $collection_id;
      if ($gcg->find(TRUE)) {
        continue;
      }
      $gcg->save();
      _civicrm_api3_object_to_array($gcg, $result);
      $gcg->free();
    }
    return $result;
  }

  /**
   * Get Centroid for a specific geometry
   * @param array $params
   * @return string
   */
  public static function getCentroid($params) {
    $result = CRM_Core_DAO::singleValueQuery("SELECT ST_AsText(ST_Centroid(geometry))
      FROM civigeometry_geometry
      WHERE id = %1", [
      1 => [$params['id'], 'Positive']]);
    return $result;
  }

  /**
   * Use ST_Contains to determine if geometry b is within geometry.
   * @param array $params
   * @return string|array
   */
  public static function contains($params) {
    $multipleResult = [];
    $duleIntegerSQL = "SELECT ST_Contains(a.geometry, b.geometry)
      FROM civigeometry_geometry a, civigeometry_geometry b
      WHERE a.id = %1, and b.id = %2";
    $singleIntergerSQL = "SELECT ST_Contains(geometry, GeomFromText(%1, 4326))
      FROM civigeometry_geometry
      WHERE id = %2";
    if ($params['geometry_a'] == 0) {
      $geometries = civicrm_api3('Geometry', 'get', ['is_active' => 1, 'options' => ['limit' => 0], 'return' => ['id']]);
      foreach ($geometries['values'] as $geometry) {
        if (is_numeric($params['geometry_b'])) {
          $res = CRM_Core_DAO::singleValueQuery($duleIntegerSQL, [
            1 => [$geometry['id'], 'Positive'],
            2 => [$params['geometry_b'], 'Positive'],
          ]);
        }
        else {
          $res = CRM_Core_DAO::singleValueQuery($singleIntergerSQL, [
            1 => [$params['geometry_b'], 'String'],
            2 => [$geometry['id'], 'Positive'],
          ]);
        }
        if (!empty($res)) {
          $multipleResult[] = $geometry['id'];
        }
      }
      return $multipleResult;
    }
    if (is_numeric($params['geometry_b'])) {
      $sql = $duleIntegerSQL;
      $sql_params = [
        1 => [$params['geometry_a'], 'Positive'],
        2 => [$params['geometry_b'], 'Positive'],
      ];
    }
    else {
      $sql = $singleIntergerSQL;
      $sql_params = [
        1 => [$params['geometry_b'], 'String'],
        2 => [$params['geometry_a'], 'Positive'],
      ];
    }
    $result = CRM_Core_DAO::singleValueQuery($sql, $sql_params);
    return $result;
  }

  /**
   * Remove a Geometry from a collection(s)
   * @param array $params
   * @return array
   */
  public static function removeGeometryFromCollection($params) {
    foreach ($params['collection_id'] as $collection_id) {
      $testGet = new CRM_CiviGeometry_DAO_GeometryCollectionGeometry();
      $testGet->geometry_id = $params['geometry_id'];
      $testGet->collection_id = $collection_id;
      $testGet->find();
      if ($testGet->N == 0) {
        throw new \Exception(E::ts("Geometry %1 is not within collection %2", [1 => $params['geometry_id'], 2 => $collection_id]));
      }
      $testGetAll = new CRM_CiviGeometry_DAO_GeometryCollectionGeometry();
      $testGetAll->geometry_id = $params['geometry_id'];
      $testGetAll->find();
      if ($testGetAll->N == 1) {
        throw new \Exception(E::ts("Geometries must belong to at least one collection"));
      }
      $gcg = new CRM_CiviGeometry_DAO_GeometryCollectionGeometry();
      $gcg->geometry_id = $params['geometry_id'];
      $gcg->collection_id = $collection_id;
      if ($gcg->find()) {
        while ($gcg->fetch()) {
          $gcg->delete();
          return civicrm_api3_create_success();
        }
      }
    }
  }

  /**
   * Archive a Geometry
   * @param array $params
   * @return CRM_CiviGeometry_DAO_Geometry Object
   */
  public static function archiveGeometry($params) {
    $instance = new CRM_CiviGeometry_DAO_Geometry();
    $instance->id = $params['id'];
    $instance->find();
    $instance->is_archived = 1;
    $instance->archived_date = date('Ymdhis');
    $instance->save();
    CRM_Utils_Hook::post('archive', 'Geometry', $instance->id, $instance);
    return $instance;
  }

  /**
   * Unarchive a Geometry
   * @param array $params
   * @return CRM_CiviGeometry_DAO_Geometry Object
   */
  public static function unarchiveGeometry($params) {
    $instance = new CRM_CiviGeometry_DAO_Geometry();
    $instance->id = $params['id'];
    $instance->find();
    $instance->is_archived = 0;
    $instance->save();
    CRM_Core_DAO::executeQuery("UPDATE civigeometry_geometry SET archived_date = NULL WHERE id = %1", [1 => [$instance->id, 'Positive']]);
    $instance->find();
    CRM_Utils_Hook::post('unarchive', 'Geometry', $instance->id, $instance);
    return $instance;
  }

  /**
   * Calculate Overlap between two geometries
   * @param array $params
   * @return array|bool
   */
  public static function calculateOverlapGeometry($params) {
    $overlap = 100;
    $checkCache = new CRM_CiviGeometry_DAO_GeometryOverlapCache();
    $checkCache->geometry_id_a = $params['geometry_id_a'];
    $checkCache->geometry_id_b = $params['geometry_id_b'];
    $checkCache->addWhere("cache_date >= DATE_SUB(NOW(), INTERVAL 1 Month)");
    $checkCache->find();
    if ($checkCache->N == 1) {
      while ($checkCache->fetch()) {
        return [
          'id' => $checkCache->id,
          'geometry_id_a' => $checkCache->geometry_id_a,
          'geometry_id_b' => $checkCache->geometry_id_b,
          'overlap' => $checkCache->overlap,
          'cache_used' => TRUE,
        ];
      }
    }
    $checkIfIntesects = CRM_Core_DAO::singleValueQuery("
      SELECT ST_Intersects(a.geometry, b.geometry)
      FROM civigeometry_geometry a, civigeometry_geometry b
      WHERE a.id = %1 AND b.id = %2", [
      1 => [$params['geometry_id_a'], 'Positive'],
      2 => [$params['geometry_id_b'], 'Positive'],
    ]);
    if (empty($checkIfIntesects)) {
      $overlap = (int) 0;
    }
    $intersections = CRM_Core_DAO::executeQuery("
      SELECT ST_Area(a.geometry) as area, ST_Area(ST_Intersection(a.geometry, b.geometry)) as intersection_area
      FROM civigeometry_geometry a, civigeometry_geometry b
      WHERE a.id = %1 AND b.id = %2", [
      1 => [$params['geometry_id_a'], 'Positive'],
      2 => [$params['geometry_id_b'], 'Positive'],
    ]);
    while ($intersections->fetch()) {
      $overlap = (int) (100.0 * $intersections->intersection_area / $intersections->area);
    }
    $overlapCache = new CRM_CiviGeometry_DAO_GeometryOverlapCache();
    $overlapCache->geometry_id_a = $params['geometry_id_a'];
    $overlapCache->geometry_id_b = $params['geometry_id_b'];
    $overlapCache->find();
    if ($overlapCache->N == 1) {
      $overlapCache->cache_date = date('Ymdhis');
    }
    $overlapCache->overlap = $overlap;
    $overlapCache->save();
    return [
      'id' => $overlapCache->id,
      'geometry_id_a' => $params['geometry_id_a'],
      'geometry_id_b' => $params['geometry_id_b'],
      'overlap' => $overlap,
      'cache_used' => FALSE,
    ];
  }

  /**
   * Calculate distance between 2 points
   * @param array $params
   * @return string
   */
  public function calculateDistance($params) {
    // We use SRID 4326 or WGS84 (SRID 4326) This is the standard projection used in google maps etc
    $result = CRM_Core_DAO::singleValueQuery("SELECT earth_circle_distance(ST_GeomFromText(%1, 4326), ST_GeomFromText(%2, 4326))", [
      1 => [$params['geometry_a'], 'String'],
      2 => [$params['geometry_b'], 'String'],
    ]);
    $meters = (float) $result * 1000;
    return $meters;
  }

}
