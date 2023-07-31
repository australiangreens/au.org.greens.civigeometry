<?php

return [
  [
    'name' => 'OptionGroup_geom_entity_relationship_reason',
    'entity' => 'OptionGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'geom_entity_relationship_reason',
        'title' => 'Geom-Entity Relationship Reasons',
        'description' => 'CiviGeometry Geometry-Entity relationship reasons',
        'data_type' => 'String',
        'is_reserved' => FALSE,
        'is_active' => TRUE,
        'is_locked' => FALSE,
      ],
      'match' => ['name'],
    ],
  ],
];
