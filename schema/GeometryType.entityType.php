<?php
use CRM_CiviGeometry_ExtensionUtil as E;
return [
  'name' => 'GeometryType',
  'table' => 'civigeometry_geometry_type',
  'class' => 'CRM_CiviGeometry_DAO_GeometryType',
  'getInfo' => fn() => [
    'title' => E::ts('Geometry Type'),
    'title_plural' => E::ts('Geometry Types'),
    'description' => E::ts('Geometry Types'),
    'log' => TRUE,
    'label_field' => 'label',
  ],
  'getIndices' => fn() => [
    'index_label' => [
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
      'description' => E::ts('Unique GeometryType ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'label' => [
      'title' => E::ts('Label'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('The title of the Geometry Type'),
    ],
    'description' => [
      'title' => E::ts('Description'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('The description of the Geometry Type'),
      'default' => 'true',
    ],
  ],
];
