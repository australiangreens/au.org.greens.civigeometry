<?php
use CRM_CiviGeometry_ExtensionUtil as E;
return [
  'name' => 'GeometryOverlapCache',
  'table' => 'civigeometry_geometry_overlap_cache',
  'class' => 'CRM_CiviGeometry_DAO_GeometryOverlapCache',
  'getInfo' => fn() => [
    'title' => E::ts('Geometry Overlap Cache'),
    'title_plural' => E::ts('Geometry Overlap Caches'),
    'description' => E::ts('Cache table containing overlaps between 2 geometries'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'index_geometry_id_a_geometry_id_b' => [
      'fields' => [
        'geometry_id_a' => TRUE,
        'geometry_id_b' => TRUE,
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
      'description' => E::ts('Unique Geometry ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'geometry_id_a' => [
      'title' => E::ts('Geometry ID A'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => E::ts('Geometry'),
      'pseudoconstant' => [
        'table_name' => 'civigeometry_geometry',
        'label_column' => 'label',
        'key_column' => 'id',
      ],
      'entity_reference' => [
        'entity' => 'Geometry',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'geometry_id_b' => [
      'title' => E::ts('Geometry ID B'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => E::ts('Geometry'),
      'pseudoconstant' => [
        'table_name' => 'civigeometry_geometry',
        'label_column' => 'label',
        'key_column' => 'id',
      ],
      'entity_reference' => [
        'entity' => 'Geometry',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'overlap' => [
      'title' => E::ts('Overlap'),
      'sql_type' => 'int',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('Overlap % that Geometry A is within Geometry B'),
      'default' => 0,
    ],
    'cache_date' => [
      'title' => E::ts('Cache Date'),
      'sql_type' => 'timestamp',
      'input_type' => 'Select Date',
      'required' => TRUE,
      'description' => E::ts('When was this overlap last re-generated'),
      'default' => 'CURRENT_TIMESTAMP()',
      'input_attrs' => [
        'format_type' => 'activityDateTime',
      ],
    ],
  ],
];
