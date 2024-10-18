<?php
use CRM_CiviGeometry_ExtensionUtil as E;
return [
  'name' => 'Geometry',
  'table' => 'civigeometry_geometry',
  'class' => 'CRM_CiviGeometry_DAO_Geometry',
  'getInfo' => fn() => [
    'title' => E::ts('Geometry'),
    'title_plural' => E::ts('Geometries'),
    'description' => E::ts('Geometries'),
    'log' => TRUE,
    'label_field' => 'label',
  ],
  'getIndices' => fn() => [
    'index_is_archived_geometry_type_label' => [
      'fields' => [
        'is_archived' => TRUE,
        'geometry_type_id' => TRUE,
        'label' => E::ts(TRUE),
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique Geometry ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'geometry_type_id' => [
      'title' => E::ts('Geometry Type'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => E::ts('Geometry Type of this geometry type'),
      'pseudoconstant' => [
        'table_name' => 'civigeometry_geometry_type',
        'label_column' => 'label',
        'key_column' => 'id',
      ],
      'entity_reference' => [
        'entity' => 'GeometryType',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'label' => [
      'title' => E::ts('Label'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('The Title of this geometry'),
    ],
    'description' => [
      'title' => E::ts('Description'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('The description of this geometry'),
      'default' => NULL,
    ],
    'is_archived' => [
      'title' => E::ts('Is Archived'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'description' => E::ts('Is this geometry archived?'),
      'default' => FALSE,
    ],
    'archived_date' => [
      'title' => E::ts('Archived Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'description' => E::ts('The Title of this geometry'),
      'default' => NULL,
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
    'geometry' => [
      'title' => E::ts('Geometry'),
      'sql_type' => 'geometry',
      'input_type' => NULL,
      'data_type' => 'String',
      'required' => TRUE,
      'description' => E::ts('The Spatial data for this geometry'),
    ],
  ],
];
