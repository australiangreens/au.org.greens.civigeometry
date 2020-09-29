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
      // We need to pass in a basic geometry point because MariaDB will not permit Spatial Indexes with NULL values and there for a value is needed for insert.
      // MySQL5.7 appears to support spatial indexes with NULL values;
      $params['geometry'] = CRM_Core_DAO::singleValueQuery("SELECT ST_geomFromText('POINT(0 0)')");
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
        1 => [$params['id'], 'Positive'],
      ]);
    return $result;
  }

  /**
   * Use ST_Contains to determine if geometry b is within geometry.
   * @param array $params
   * @return string|array
   */
  public static function contains($params) {
    $multipleResult = [];
    $dualIntegerSQL = "SELECT a.id, ST_Contains(a.geometry, b.geometry) as contains_result
        FROM civigeometry_geometry a, civigeometry_geometry b";
    $dualIntegerWhere = " WHERE b.id = %2 AND a.is_archived = 0 AND b.is_archived = 0";
    $singleIntegerSQL = "SELECT cg.id, ST_Contains(cg.geometry, GeomFromText(%1, 4326)) as contains_result
      FROM civigeometry_geometry cg";
    $singleIntegerWhere = " WHERE cg.is_archived = 0";
    if ($params['geometry_a'] == 0) {
      $dualIntegerParams = [
        2 => [$params['geometry_b'], 'Positive'],
      ];
      $singleIntegerParams = [
        1 => [$params['geometry_b'], 'String'],
      ];
      if (!empty($params['geometry_a_collection_id'])) {
        $singleIntegerSQL .= " INNER JOIN civigeometry_geometry_collection_geometry cgc ON cgc.geometry_id = cg.id";
        $singleIntegerWhere = " AND cgc.collection_id = %2";
        $singleIntegerParams[2] = [$params['geometry_a_collection_id'], 'Positive'];
        $dualIntegerSQL .= " INNER JOIN civigeometry_geometry_collection_geometry cgc ON cgc.geometry_id = a.id";
        $dualIntegerWhere .= " AND cgc.collection_id = %1";
        $dualIntegerParams[1] = [$params['geometry_a_collection_id'], 'Positive'];
      }
      if (is_numeric($params['geometry_b'])) {
        $res = CRM_Core_DAO::executeQuery($dualIntegerSQL . $dualIntegerWhere, $dualIntegerParams);
      }
      else {
        $res = CRM_Core_DAO::executeQuery($singleIntegerSQL . $singleIntegerWhere, $singleIntegerParams);
      }
      while ($res->fetch()) {
        if ($res->contains_result) {
          $multipleResult[] = $res->id;
        }
      }
      return $multipleResult;
    }
    if (is_numeric($params['geometry_b'])) {
      $sql = $dualIntegerSQL . $dualIntegerWhere . " AND a.id = %1";
      $sql_params = [
        1 => [$params['geometry_a'], 'Positive'],
        2 => [$params['geometry_b'], 'Positive'],
      ];
    }
    else {
      $sql = $singleIntegerSQL . $singleIntegerWhere . " AND cg.id = %2";
      $sql_params = [
        1 => [$params['geometry_b'], 'String'],
        2 => [$params['geometry_a'], 'Positive'],
      ];
    }
    $result = CRM_Core_DAO::executeQuery($sql, $sql_params);
    while ($result->fetch()) {
      return $result->contains_result;
    }
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
   * Check if geometry pair intersects or get all the geometries in the specified collection that intersects
   * @param array $params
   * @return array
   *   - Array of geometries that intersect
   *   - geometry_a
   *   - geometry_b
   */
  public static function getGeometryIntersection($params) {
    $values = [];
    if (!empty($params['collection_id'])) {
      $select_table = 'b';
      $where_table = 'a';
      if (!empty($params['geometry_b']) && empty($params['geometry_a'])) {
        $select_table = 'a';
        $where_table = 'b';
      }
      $query_params = [2 => [$params['collection_id'], 'Positive']];
      if ($select_table === 'a') {
        $query_params[1] = [$params['geometry_b'], 'Positive'];
      }
      else {
        $query_params[1] = [$params['geometry_a'], 'Positive'];
      }
      $geometries = CRM_Core_DAO::executeQuery("SELECT {$select_table}.id
        FROM civigeometry_geometry {$where_table}, civigeometry_geometry as {$select_table}
        INNER JOIN civigeometry_geometry_collection_geometry cgc ON cgc.geometry_id = {$select_table}.id
        WHERE {$where_table}.id = %1 AND cgc.collection_id = %2 AND ST_Intersects(a.geometry, b.geometry) != 0", $query_params)->fetchAll();
      foreach ($geometries as $geometry_id) {
        if ($select_table === 'a') {
          $values[] = [
            'geometry_a' => $geometry_id['id'],
            'geometry_b' => $params['geometry_b'],
          ];
        }
        else {
          $values[] = [
            'geometry_a' => $params['geometry_a'],
            'geometry_b' => $geometry_id['id'],
          ];
        }
      }
    }
    else {
      $intersection = CRM_Core_DAO::singleValueQuery("SELECT ST_Intersects(a.geometry, b.geometry)
        FROM civigeometry_geometry a, civigeometry_geometry b
        WHERE a.id = %1 AND b.id = %2", [
          1 => [$params['geometry_a'], 'Positive'],
          2 => [$params['geometry_b'], 'Positive'],
        ]);
      if ($intersection) {
        $values[] = [
          'geometry_a' => $params['geometry_a'],
          'geometry_b' => $params['geometry_b'],
        ];
      }
    }
    return $values;
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
    $checkCache->find();
    if ($checkCache->N == 1) {
      while ($checkCache->fetch()) {
        $overlap = (int) $checkCache->overlap;
        if (empty($params['overlap']) || (!empty($params['overlap']) && $overlap >= (int) $params['overlap'])) {
          return [
            'id' => $checkCache->id,
            'geometry_id_a' => $checkCache->geometry_id_a,
            'geometry_id_b' => $checkCache->geometry_id_b,
            'overlap' => $checkCache->overlap,
            'cache_used' => TRUE,
          ];
        }
        return [];
      }
    }
    $intersectionArea = CRM_Core_DAO::executeQuery("
      SELECT ST_Area(a.geometry) as area, ST_Area(ST_Intersection(a.geometry, b.geometry)) as intersection_area
      FROM civigeometry_geometry a, civigeometry_geometry b
      WHERE a.id = %1 AND b.id = %2", [
        1 => [$params['geometry_id_a'], 'Positive'],
        2 => [$params['geometry_id_b'], 'Positive'],
      ]);
    while ($intersectionArea->fetch()) {
      $overlap = (int) (100.0 * $intersectionArea->intersection_area / $intersectionArea->area);
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
    if (empty($params['overlap']) || (!empty($params['overlap']) && (int) $overlap >= (int) $params['overlap'])) {
      return [
        'id' => $overlapCache->id,
        'geometry_id_a' => $params['geometry_id_a'],
        'geometry_id_b' => $params['geometry_id_b'],
        'overlap' => $overlap,
        'cache_used' => FALSE,
      ];
    }
    return [];
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
    $metres = (float) $result * 1000;
    return $metres;
  }

  /**
   * Return Spatial Properties for a geometry
   * @param int $geometryID
   * @return array
   *   - Containing, approximate Square Kms of the Geometry
   *   - The Envelope of the geometry
   *   - The Centroid of the geometry
   *   - Is it a simple geometry
   *   - the SRID of the geometry
   */
  public static function returnSpatialInformation($geometryID) {
    return CRM_Core_DAO::executeQuery("SELECT
        id,
        label
        , round((ST_Area(geometry)*10000), 3) as square_km
        , ST_AsText(ST_Envelope(geometry)) as ST_Envelope
        , ST_AsText(ST_Centroid(geometry)) as ST_Centroid
        , ST_IsSimple (geometry) as ST_IsSimple
        , ST_SRID(geometry) as ST_SRID
      FROM %1
      WHERE id = %2", [
        1 => [self::getTableName(), 'MysqlColumnNameOrAlias'],
        2 => [$geometryID, 'Positive'],
      ])->fetchAll()[0];
  }

  /**
   * Return the min and max x and y points for a geometry
   * @param int $geometryID
   *
   * @return array
   */
  public static function generateBounds($geometryID) {
    $envelope = CRM_Core_DAO::singleValueQuery("SELECT ST_AsText(ST_Envelope(geometry)) FROM " . self::getTableName() . " WHERE id = %1", [
      1 => [$geometryID, 'Positive'],
    ]);
    $envelopePieces = explode(',', substr($envelope, 9, -2));
    $leftBound = $rightBound = $topBound = $bottomBound = 0;
    foreach ($envelopePieces as $key => $piece) {
      if ($key == 0 || $key == 2) {
        $pieces = explode(' ', $piece);
        if ($key == 0) {
          $leftBound = $pieces[0];
          $bottomBound = $pieces[1];
        }
        else {
          $topBound = $pieces[1];
          $rightBound = $pieces[0];
        }
      }
    }
    return ['left_bound' => $leftBound, 'bottom_bound' => $bottomBound, 'top_bound' => $topBound, 'right_bound' => $rightBound];
  }

  /**
   * Convert Wkt Geoemtry to KML
   * @param string $wkt
   * @see http://blog.mastermaps.com/2008/03/wkt-to-kml-transformation.html
   * @return string KML Geoemtry
   */
  public static function wkt2kml($wkt) {
    // Change coordinate format
    $wkt = preg_replace("/([0-9\.\-]+) ([0-9\.\-]+),*/", "$1,$2 ", $wkt);

    $wkt = substr($wkt, 15);
    $wkt = substr($wkt, 0, -3);
    $polygons = explode(')),((', $wkt);
    $kml = '<MultiGeometry>';

    foreach ($polygons as $polygon) {
      $kml .= '<Polygon>';
      $boundary = explode('),(', $polygon);
      if ($boundary[0]) {
        $kml .= '<outerBoundaryIs>'
          . '<LinearRing>'
          . '<coordinates>' . $boundary[0] . '</coordinates>'
          . '</LinearRing>'
          . '</outerBoundaryIs>';
      }
      else {
        return '';
      }
      for ($i = 1; $i < count($boundary); $i++) {
        $kml .= '<innerBoundaryIs>'
          . '<LinearRing>'
          . '<coordinates>' . $boundary[$i] . '</coordinates>'
          . '</LinearRing>'
          . '</innerBoundaryIs>';
      }
      $kml .= '</Polygon>';
    }
    $kml .= '</MultiGeometry>';
    return $kml;
  }

  /**
   * Get an an array of address ids for a specific geometry_id
   * @param int $geometry_id
   * @return array
   */
  public static function getAddresses($geometry_id) {
    $bounds = civicrm_api3('Geometry', 'getbounds', ['id' => $geometry_id])['values'][$geometry_id];
    $select = CRM_Utils_SQL_Select::from('civicrm_address')
      ->select("id")
      ->where("geo_code_2 >= '#left_bound'", ['left_bound' => $bounds['left_bound']])
      ->where("geo_code_2 <= '#right_bound'", ['right_bound' => $bounds['right_bound']])
      ->where("geo_code_1 <= '#top_bound'", ['top_bound' => $bounds['top_bound']])
      ->where("geo_code_1 >= '#bottom_bound'", ['bottom_bound' => $bounds['bottom_bound']]);
    $addressResults = CRM_Core_DAO::executeQuery($select->toSQL())->fetchAll();
    $results = [];
    if (!empty($addressResults)) {
      $address_ids = CRM_Utils_Array::collect('id', $addressResults);
      foreach ($address_ids as $address_id) {
        $select = CRM_Utils_SQL_Select::from(self::getTableName() . ' g, civicrm_address ca')
          ->select("ca.id as address_id, g.id as geometry_id")
          ->where("ST_Contains(g.geometry, ST_GeomFromText(CONCAT('POINT(', ca.geo_code_2, ' ', ca.geo_code_1, ')'), 4326)) = 1")
          ->where("g.id = #geometry_id", ['geometry_id' => $geometry_id])
          ->where("ca.id = #address_id", ['address_id' => $address_id]);
        $result = CRM_Core_DAO::executeQuery($select->toSQL())->fetchAll();
        if (!empty($result)) {
          $results[] = $result[0];
        }
      }
      return $results;
    }
    else {
      return [];
    }
  }

  /**
   * Get Geometries that are within a specified distance
   * We use the centroid of geometries in an effort to ensure that we have a stable point to check the distance against
   * ST_Distance in MySQL is not as perfect as in PostGis
   * @param array $params
   * @return array
   */
  public static function getNearestGeometries($params) {
    $select = CRM_Utils_SQL_Select::from(self::getTableName() . ' g')
      ->select("g.id")
      ->where("earth_circle_distance(ST_GeomFromText(@point, 4326), ST_GeomFromText(ST_AsText(ST_Centroid(g.geometry)), 4326)) <= #distance", [
        'point' => $params['point'],
        'distance' => $params['distance'],
      ])
      ->orderBy("earth_circle_distance(ST_GeomFromText(@point, 4326), ST_GeomFromText(ST_AsText(ST_Centroid(g.geometry)), 4326))", [
        'point' => $params['point'],
      ]);
    if (!empty($params['collection_id'])) {
      $select->join("gcg", 'INNER JOIN civigeoemtry_geometry_collection_geometry cgc ON cgc.geometry_id = g.id');
      $select->where('gcg.collection_id = #collection_id', ['collection_id' => $params['collection_id']]);
    }
    if (!empty($params['geometry_id'])) {
      $value = $params['geometry_id'];
      $operator = is_array($value) ? \CRM_Utils_Array::first(array_keys($value)) : NULL;
      if (!in_array($operator, \CRM_Core_DAO::acceptedSQLOperators(), TRUE)) {
        $value = ['=' => $value];
      }
      $select->where(CRM_Core_DAO::createSQLFilter('g.id', $value));
    }
    $options = _civicrm_api3_get_options_from_params($params);
    $select->limit($options['limit'], $options['offset']);
    $results = CRM_Core_DAO::executeQuery($select->toSQL())->fetchAll();
    if (!empty($results)) {
      return $results;
    }
    return [];
  }

}
