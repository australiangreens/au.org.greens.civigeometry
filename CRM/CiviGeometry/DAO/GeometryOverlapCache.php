<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from au.org.greens.civigeometry/xml/schema/CRM/CiviGeometry/GeometryOverlapCache.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:153069bedd01a3b7226e80d305810fca)
 */
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Database access object for the GeometryOverlapCache entity.
 */
class CRM_CiviGeometry_DAO_GeometryOverlapCache extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civigeometry_geometry_overlap_cache';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique Geometry ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * Geometry
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $geometry_id_a;

  /**
   * Geometry
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $geometry_id_b;

  /**
   * Overlap % that Geometry A is within Geometry B
   *
   * @var int|string
   *   (SQL type: int)
   *   Note that values will be retrieved from the database as a string.
   */
  public $overlap;

  /**
   * When was this overlap last re-generated
   *
   * @var string
   *   (SQL type: timestamp)
   *   Note that values will be retrieved from the database as a string.
   */
  public $cache_date;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civigeometry_geometry_overlap_cache';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Geometry Overlap Caches') : E::ts('Geometry Overlap Cache');
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('ID'),
          'description' => E::ts('Unique Geometry ID'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civigeometry_geometry_overlap_cache.id',
          'table_name' => 'civigeometry_geometry_overlap_cache',
          'entity' => 'GeometryOverlapCache',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryOverlapCache',
          'localizable' => 0,
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'geometry_id_a' => [
          'name' => 'geometry_id_a',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Geometry ID A'),
          'description' => E::ts('Geometry'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civigeometry_geometry_overlap_cache.geometry_id_a',
          'table_name' => 'civigeometry_geometry_overlap_cache',
          'entity' => 'GeometryOverlapCache',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryOverlapCache',
          'localizable' => 0,
          'FKClassName' => 'CRM_CiviGeometry_DAO_Geometry',
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'keyColumn' => 'id',
            'labelColumn' => 'label',
          ],
          'add' => NULL,
        ],
        'geometry_id_b' => [
          'name' => 'geometry_id_b',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Geometry ID B'),
          'description' => E::ts('Geometry'),
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civigeometry_geometry_overlap_cache.geometry_id_b',
          'table_name' => 'civigeometry_geometry_overlap_cache',
          'entity' => 'GeometryOverlapCache',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryOverlapCache',
          'localizable' => 0,
          'FKClassName' => 'CRM_CiviGeometry_DAO_Geometry',
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'keyColumn' => 'id',
            'labelColumn' => 'label',
          ],
          'add' => NULL,
        ],
        'overlap' => [
          'name' => 'overlap',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Overlap'),
          'description' => E::ts('Overlap % that Geometry A is within Geometry B'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civigeometry_geometry_overlap_cache.overlap',
          'default' => '0',
          'table_name' => 'civigeometry_geometry_overlap_cache',
          'entity' => 'GeometryOverlapCache',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryOverlapCache',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'cache_date' => [
          'name' => 'cache_date',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => E::ts('Cache Date'),
          'description' => E::ts('When was this overlap last re-generated'),
          'required' => TRUE,
          'usage' => [
            'import' => FALSE,
            'export' => FALSE,
            'duplicate_matching' => FALSE,
            'token' => FALSE,
          ],
          'where' => 'civigeometry_geometry_overlap_cache.cache_date',
          'default' => 'CURRENT_TIMESTAMP()',
          'table_name' => 'civigeometry_geometry_overlap_cache',
          'entity' => 'GeometryOverlapCache',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryOverlapCache',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
          ],
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'etry_geometry_overlap_cache', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'etry_geometry_overlap_cache', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [
      'index_geometry_id_a_geometry_id_b' => [
        'name' => 'index_geometry_id_a_geometry_id_b',
        'field' => [
          0 => 'geometry_id_a',
          1 => 'geometry_id_b',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civigeometry_geometry_overlap_cache::1::geometry_id_a::geometry_id_b',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
