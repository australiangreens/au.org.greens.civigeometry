<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 *
 * Generated from /home/seamus/buildkit/build/47-test/sites/default/files/civicrm/ext/au.org.greens.civigeometry/xml/schema/CRM/CiviGeometry/GeometryCollectionType.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:e34a0bcfd26fe6a93087f571b663b93c)
 */

/**
 * Database access object for the GeometryCollectionType entity.
 */
class CRM_CiviGeometry_DAO_GeometryCollectionType extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civigeometry_geometry_collection_type';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique GeometryCollectionType ID
   *
   * @var int
   */
  public $id;

  /**
   * Title of the Geometry Collection Type
   *
   * @var string
   */
  public $label;

  /**
   * Title of the Geometry Collection Type
   *
   * @var string
   */
  public $description;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civigeometry_geometry_collection_type';
    parent::__construct();
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
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('Unique GeometryCollectionType ID'),
          'required' => TRUE,
          'where' => 'civigeometry_geometry_collection_type.id',
          'table_name' => 'civigeometry_geometry_collection_type',
          'entity' => 'GeometryCollectionType',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryCollectionType',
          'localizable' => 0,
        ],
        'label' => [
          'name' => 'label',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_CiviGeometry_ExtensionUtil::ts('Label'),
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('Title of the Geometry Collection Type'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civigeometry_geometry_collection_type.label',
          'table_name' => 'civigeometry_geometry_collection_type',
          'entity' => 'GeometryCollectionType',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryCollectionType',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
        ],
        'description' => [
          'name' => 'description',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_CiviGeometry_ExtensionUtil::ts('Description'),
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('Title of the Geometry Collection Type'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civigeometry_geometry_collection_type.description',
          'default' => 'NULL',
          'table_name' => 'civigeometry_geometry_collection_type',
          'entity' => 'GeometryCollectionType',
          'bao' => 'CRM_CiviGeometry_DAO_GeometryCollectionType',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'etry_geometry_collection_type', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'etry_geometry_collection_type', $prefix, []);
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
      'UI_label' => [
        'name' => 'UI_label',
        'field' => [
          0 => 'label',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civigeometry_geometry_collection_type::1::label',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
