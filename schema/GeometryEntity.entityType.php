<?php
use CRM_CiviGeometry_ExtensionUtil as E;
return [
  'name' => 'GeometryEntity',
  'table' => 'civigeometry_geometry_entity',
  'class' => 'CRM_CiviGeometry_DAO_GeometryEntity',
  'getInfo' => fn() => [
    'title' => E::ts('Geometry Entity'),
    'title_plural' => E::ts('Geometry Entities'),
    'description' => E::ts('Holds a static cache of geometry ids an address is within'),
    'log' => TRUE,
  ],
  'getIndices' => fn() => [
    'index_entity_table_geometry_id_entity_id' => [
      'fields' => [
        'entity_table' => TRUE,
        'geometry_id' => TRUE,
        'entity_id' => TRUE,
      ],
      'unique' => TRUE,
    ],
    'index_expiry_date' => [
      'fields' => [
        'expiry_date' => TRUE,
      ],
    ],
    'index_entity_table_entity_id' => [
      'fields' => [
        'entity_table' => TRUE,
        'entity_id' => TRUE,
      ],
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique GeometryEntity ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'entity_id' => [
      'title' => E::ts('Entity ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('entity id that is associated with this geometry'),
      'entity_reference' => [
        'dynamic_entity' => 'entity_table',
        'key' => 'id',
      ],
    ],
    'entity_table' => [
      'title' => E::ts('Entity Table'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Select',
      'required' => TRUE,
      'description' => E::ts('entity table that is associated with this geometry'),
      'pseudoconstant' => [
        'callback' => 'CRM_CiviGeometry_Utils::getSupportedEntities',
      ],
    ],
    'geometry_id' => [
      'title' => E::ts('Geometry ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'EntityRef',
      'required' => TRUE,
      'description' => E::ts('FK to Geometry Table'),
      'entity_reference' => [
        'entity' => 'Geometry',
        'key' => 'id',
        'on_delete' => 'CASCADE',
      ],
    ],
    'expiry_date' => [
      'title' => E::ts('Expiry Date'),
      'sql_type' => 'timestamp',
      'input_type' => NULL,
      'description' => E::ts('When Should this geometry entity relationship expire'),
      'default' => NULL,
    ],
    'reason' => [
      'title' => E::ts('Reason'),
      'sql_type' => 'varchar(64)',
      'input_type' => 'Select',
      'description' => E::ts('The reason for the relationship between the geometry and entity'),
      'default' => NULL,
      'pseudoconstant' => [
        'option_group_name' => 'OptionGroup_geom_entity_relationship_reason',
      ],
    ],
  ],
];
