ALTER TABLE civigeometry_geometry_entity ADD COLUMN `expiry_date` timestamp NULL  DEFAULT NULL COMMENT 'When Should this geometry entity relationship expire';
ALTER TABLE civigeometry_geometry_entity ADD INDEX `index_expiry_date`(`expiry_date`);
