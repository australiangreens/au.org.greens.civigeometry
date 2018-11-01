<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2018
 *
 * Generated from /home/seamus/buildkit/build/47-test/sites/default/files/civicrm/ext/au.org.greens.civigeometry/xml/schema/CRM/CiviGeometry/Geometry.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:1148804a6fa1ea1618be91243a414dbc)
 */

/**
 * Database access object for the Geometry entity.
 */
class CRM_CiviGeometry_DAO_Geometry extends CRM_Core_DAO {

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  static $_tableName = 'civigeometry_geometry';

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
   * Geometry Type of this geometry type
   *
   * @var int unsigned
   */
  public $geometry_type_id;

  /**
   * The Title of this geometry
   *
   * @var string
   */
  public $label;

  /**
   * The description of this geometry
   *
   * @var string
   */
  public $description;

  /**
   * Is this geometry archived?
   *
   * @var boolean
   */
  public $is_archived;

  /**
   * The Title of this geometry
   *
   * @var timestamp
   */
  public $archive_date;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civigeometry_geometry';
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
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'geometry_type_id', 'civigeometry_geometry_type', 'id');
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
          'table_name' => 'civigeometry_geometry',
          'entity' => 'Geometry',
          'bao' => 'CRM_CiviGeometry_DAO_Geometry',
          'localizable' => 0,
        ],
        'geometry_type_id' => [
          'name' => 'geometry_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => CRM_CiviGeometry_ExtensionUtil::ts('Geometry Type'),
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('Geometry Type of this geometry type'),
          'table_name' => 'civigeometry_geometry',
          'entity' => 'Geometry',
          'bao' => 'CRM_CiviGeometry_DAO_Geometry',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'keyColumn' => 'id',
            'labelColumn' => 'label',
          ]
        ],
        'label' => [
          'name' => 'label',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_CiviGeometry_ExtensionUtil::ts('Label'),
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('The Title of this geometry'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'table_name' => 'civigeometry_geometry',
          'entity' => 'Geometry',
          'bao' => 'CRM_CiviGeometry_DAO_Geometry',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
        ],
        'description' => [
          'name' => 'description',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => CRM_CiviGeometry_ExtensionUtil::ts('Description'),
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('The description of this geometry'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'default' => 'NULL',
          'table_name' => 'civigeometry_geometry',
          'entity' => 'Geometry',
          'bao' => 'CRM_CiviGeometry_DAO_Geometry',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
        ],
        'is_archived' => [
          'name' => 'is_archived',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('Is this geometry archived?'),
          'default' => '0',
          'table_name' => 'civigeometry_geometry',
          'entity' => 'Geometry',
          'bao' => 'CRM_CiviGeometry_DAO_Geometry',
          'localizable' => 0,
          'html' => [
            'type' => 'CheckBox',
          ],
        ],
        'archive_date' => [
          'name' => 'archive_date',
          'type' => CRM_Utils_Type::T_TIMESTAMP,
          'title' => CRM_CiviGeometry_ExtensionUtil::ts('Archive Date'),
          'description' => CRM_CiviGeometry_ExtensionUtil::ts('The Title of this geometry'),
          'required' => FALSE,
          'default' => 'NULL',
          'table_name' => 'civigeometry_geometry',
          'entity' => 'Geometry',
          'bao' => 'CRM_CiviGeometry_DAO_Geometry',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'activityDateTime',
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'etry_geometry', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'etry_geometry', $prefix, []);
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
      'index_geometry_type_label' => [
        'name' => 'index_geometry_type_label',
        'field' => [
          0 => 'geometry_type_id',
          1 => 'label',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civigeometry_geometry::1::geometry_type_id::label',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
