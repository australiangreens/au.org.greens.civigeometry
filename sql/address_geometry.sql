CREATE TABLE `civigeometry_address_geometry` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique AddressGeometry ID',
     `address_id` int unsigned NOT NULL   COMMENT 'FK to Address Table',
     `geometry_id` int unsigned NOT NULL   COMMENT 'FK to Geometry Table'
,
        PRIMARY KEY (`id`)


,          CONSTRAINT FK_civicrm_address_geometry_address_id FOREIGN KEY (`address_id`) REFERENCES `civicrm_address`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civicrm_address_geometry_geometry_id FOREIGN KEY (`geometry_id`) REFERENCES `civigeometry_geometry`(`id`) ON DELETE CASCADE
)    ;

