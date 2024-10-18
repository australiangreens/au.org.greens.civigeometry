<?php
use CRM_CiviGeometry_ExtensionUtil as E;
return [
  'name' => 'GeometryCollection',
  'table' => 'civigeometry_geometry_collection',
  'class' => 'CRM_CiviGeometry_DAO_GeometryCollection',
  'getInfo' => fn() => [
    'title' => E::ts('Geometry Collection'),
    'title_plural' => E::ts('Geometry Collections'),
    'description' => E::ts('Details on a collection of Geometries'),
    'log' => TRUE,
    'label_field' => 'label',
  ],
  'getIndices' => fn() => [
    'index_type_id_label' => [
      'fields' => [
        'geometry_collection_type_id' => TRUE,
        'label' => E::ts(TRUE),
      ],
      'unique' => TRUE,
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique GeometryCollection ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'geometry_collection_type_id' => [
      'title' => E::ts('Geometry Collection Type ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'required' => TRUE,
      'description' => E::ts('FK to civigeomety_geometry_collection_type'),
      'pseudoconstant' => [
        'table' => 'civigeometry_geometry_collection_type',
        'key_column' => 'id',
        'label_column' => 'label',
      ],
      'entity_reference' => [
        'entity' => 'GeometryCollectionType',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'label' => [
      'title' => E::ts('Label'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Title of the Geometry Collection'),
    ],
    'description' => [
      'title' => E::ts('Description'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Description of the Geometry Collection'),
      'default' => NULL,
    ],
    'source' => [
      'title' => E::ts('Source'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Source of the Geometry Collection'),
      'default' => NULL,
    ],
    'is_archived' => [
      'title' => E::ts('Is Archived'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'description' => E::ts('Is this Geometry Collection archived'),
      'default' => FALSE,
    ],
    'archived_date' => [
      'title' => E::ts('Archived Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'description' => E::ts('When was this Geometry Collection archived'),
      'default' => NULL,
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
  ],
];
