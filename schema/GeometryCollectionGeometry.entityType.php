<?php
use CRM_CiviGeometry_ExtensionUtil as E;
return [
  'name' => 'GeometryCollectionGeometry',
  'table' => 'civigeometry_geometry_collection_geometry',
  'class' => 'CRM_CiviGeometry_DAO_GeometryCollectionGeometry',
  'getInfo' => fn() => [
    'title' => E::ts('Geometry Collection Geometry'),
    'title_plural' => E::ts('Geometry Collection Geometries'),
    'description' => E::ts('Linkage between Geometries and their collections'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'index_collection_id_geometry_id' => [
      'fields' => [
        'collection_id' => TRUE,
        'geometry_id' => TRUE,
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
    'geometry_id' => [
      'title' => E::ts('Geometry ID'),
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
    'collection_id' => [
      'title' => E::ts('Geometry Collection ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Select',
      'description' => E::ts('Geometry Collection'),
      'pseudoconstant' => [
        'table_name' => 'civigeometry_geometry_collection',
        'label_column' => 'label',
        'key_column' => 'id',
      ],
      'entity_reference' => [
        'entity' => 'GeometryCollection',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
  ],
];
