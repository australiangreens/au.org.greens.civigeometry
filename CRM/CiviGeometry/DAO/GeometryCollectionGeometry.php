<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 *
 * Generated from /home/seamus/buildkit/build/47-test/sites/default/files/civicrm/ext/au.org.greens.civigeometry/xml/schema/CRM/CiviGeometry/GeometryCollectionGeometry.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:b85a886171863cac618e8af758f80a5b)
 */

/**
 * Database access object for the GeometryCollectionGeometry entity.
 */
class CRM_CiviGeometry_DAO_GeometryCollectionGeometry extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  static $_tableName = 'civigeometry_geometry_collection_geometry';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  static $_log = TRUE;

  /**
   * Unique Geometry ID
   *
   * @var int unsigned
   */
  public $id;

  /**
   * Geometry
   *
   * @var int unsigned
   */
  public $geometry_id;

  /**
   * Geometry Collection
   *
   * @var int unsigned
   */
  public $collection_id;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civigeometry_geometry_collection_geometry';
    parent::__construct();
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static ::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'geometry_id', 'civigeometry_geometry', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'collection_id', 'civigeometry_geometry_collection', 'id');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
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
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('Unique Geometry ID'),
          'required' => TRUE,
          'table_name' => 'civigeometry_geometry_collection_geometry',
          'entity' => 'GeometryCollectionGeometry',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryCollectionGeometry',
          'localizable' => 0,
        ],
        'geometry_id' => [
          'name' => 'geometry_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => CRM_CiviGeometry_ExtensionUtil::ts('Geometry ID'),
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('Geometry'),
          'table_name' => 'civigeometry_geometry_collection_geometry',
          'entity' => 'GeometryCollectionGeometry',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryCollectionGeometry',
          'localizable' => 0,
          'FKClassName' => 'CRM_CiviGeometry_DAO_Geometry',
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'keyColumn' => 'id',
            'labelColumn' => 'label',
          ]
        ],
        'collection_id' => [
          'name' => 'collection_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => CRM_CiviGeometry_ExtensionUtil::ts('Geometry Collection ID'),
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('Geometry Collection'),
          'table_name' => 'civigeometry_geometry_collection_geometry',
          'entity' => 'GeometryCollectionGeometry',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryCollectionGeometry',
          'localizable' => 0,
          'FKClassName' => 'CRM_CiviGeometry_DAO_GeometryCollection',
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'keyColumn' => 'id',
            'labelColumn' => 'label',
          ]
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'etry_geometry_collection_geometry', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'etry_geometry_collection_geometry', $prefix, []);
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
      'index_geometry_id_collection_id' => [
        'name' => 'index_geometry_id_collection_id',
        'field' => [
          0 => 'geometry_id',
          1 => 'collection_id',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civigeometry_geometry_collection_geometry::1::geometry_id::collection_id',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}