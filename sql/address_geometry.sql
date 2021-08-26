-- /*******************************************************
-- *
-- * civigeometry_address_geometry
-- *
-- * Holds a static cache of geometry ids an address is within
-- *
-- *******************************************************/
CREATE TABLE `civigeometry_address_geometry` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique AddressGeometry ID',
     `address_id` int unsigned NOT NULL   COMMENT 'FK to Address Table',
     `geometry_id` int unsigned NOT NULL   COMMENT 'FK to Geometry Table'
,
        PRIMARY KEY (`id`)

    ,     UNIQUE INDEX `UI_geometry_id_address_id`(
        geometry_id
      , address_id
  )

,          CONSTRAINT FK_civigeometry_address_geometry_address_id FOREIGN KEY (`address_id`) REFERENCES `civicrm_address`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civigeometry_address_geometry_geometry_id FOREIGN KEY (`geometry_id`) REFERENCES `civigeometry_geometry`(`id`) ON DELETE CASCADE
)    ;
