<?php
use CRM_CiviGeometry_ExtensionUtil as E;
return [
  'name' => 'GeometryCollectionType',
  'table' => 'civigeometry_geometry_collection_type',
  'class' => 'CRM_CiviGeometry_DAO_GeometryCollectionType',
  'getInfo' => fn() => [
    'title' => E::ts('Geometry Collection Type'),
    'title_plural' => E::ts('Geometry Collection Types'),
    'description' => E::ts('Geometry collection types'),
    'log' => TRUE,
    'label_field' => 'label',
  ],
  'getIndices' => fn() => [
    'UI_label' => [
      'fields' => [
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
      'description' => E::ts('Unique GeometryCollectionType ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'label' => [
      'title' => E::ts('Label'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Title of the Geometry Collection Type'),
    ],
    'description' => [
      'title' => E::ts('Description'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('Title of the Geometry Collection Type'),
      'default' => NULL,
    ],
  ],
];
