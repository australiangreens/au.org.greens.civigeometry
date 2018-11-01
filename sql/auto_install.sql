-- +--------------------------------------------------------------------+
-- | CiviCRM version 5                                                  |
-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC (c) 2004-2018                                |
-- +--------------------------------------------------------------------+
-- | This file is a part of CiviCRM.                                    |
-- |                                                                    |
-- | CiviCRM is free software; you can copy, modify, and distribute it  |
-- | under the terms of the GNU Affero General Public License           |
-- | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
-- |                                                                    |
-- | CiviCRM is distributed in the hope that it will be useful, but     |
-- | WITHOUT ANY WARRANTY; without even the implied warranty of         |
-- | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
-- | See the GNU Affero General Public License for more details.        |
-- |                                                                    |
-- | You should have received a copy of the GNU Affero General Public   |
-- | License and the CiviCRM Licensing Exception along                  |
-- | with this program; if not, contact CiviCRM LLC                     |
-- | at info[AT]civicrm[DOT]org. If you have questions about the        |
-- | GNU Affero General Public License or the licensing of CiviCRM,     |
-- | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--


-- +--------------------------------------------------------------------+
-- | CiviCRM version 5                                                  |
-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC (c) 2004-2018                                |
-- +--------------------------------------------------------------------+
-- | This file is a part of CiviCRM.                                    |
-- |                                                                    |
-- | CiviCRM is free software; you can copy, modify, and distribute it  |
-- | under the terms of the GNU Affero General Public License           |
-- | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
-- |                                                                    |
-- | CiviCRM is distributed in the hope that it will be useful, but     |
-- | WITHOUT ANY WARRANTY; without even the implied warranty of         |
-- | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
-- | See the GNU Affero General Public License for more details.        |
-- |                                                                    |
-- | You should have received a copy of the GNU Affero General Public   |
-- | License and the CiviCRM Licensing Exception along                  |
-- | with this program; if not, contact CiviCRM LLC                     |
-- | at info[AT]civicrm[DOT]org. If you have questions about the        |
-- | GNU Affero General Public License or the licensing of CiviCRM,     |
-- | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
-- +--------------------------------------------------------------------+
--
-- Generated from drop.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the exisiting tables
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civigeometry_geometry_type`;
DROP TABLE IF EXISTS `civigeometry_geometry_collection_type`;
DROP TABLE IF EXISTS `civigeometry_geometry_collection_geometry`;
DROP TABLE IF EXISTS `civigeometry_geometry_type`;
DROP TABLE IF EXISTS `civigeometry_geometry`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civigeometry_geometry_collection_type
-- *
-- * Geometry collection types
-- *
-- *******************************************************/
CREATE TABLE `civigeometry_geometry_collection_type` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique GeometryCollectionType ID',
     `label` varchar(255) NOT NULL   COMMENT 'Title of the Geometry Collection Type',
     `description` varchar(255)   DEFAULT NULL COMMENT 'Title of the Geometry Collection Type' 
,
        PRIMARY KEY (`id`)
 
    ,     UNIQUE INDEX `UI_label`(
        label
  )
  
 
)    ;

-- /*******************************************************
-- *
-- * civigeometry_geometry_type
-- *
-- * Geometry Types
-- *
-- *******************************************************/
CREATE TABLE `civigeometry_geometry_type` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique GeometryType ID',
     `label` varchar(255) NOT NULL   COMMENT 'The title of the Geometry Type',
     `description` varchar(255)   DEFAULT true COMMENT 'The description of the Geometry Type' 
,
        PRIMARY KEY (`id`)
 
    ,     UNIQUE INDEX `index_label`(
        label
  )
  
 
)    ;

-- /*******************************************************
-- *
-- * civigeometry_geometry
-- *
-- * Geometries
-- *
-- *******************************************************/
CREATE TABLE `civigeometry_geometry` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Geometry ID',
     `geometry_type_id` int unsigned    COMMENT 'Geometry Type of this geometry type',
     `label` varchar(255) NOT NULL   COMMENT 'The Title of this geometry',
     `description` varchar(255)   DEFAULT NULL COMMENT 'The description of this geometry',
     `is_archived` tinyint   DEFAULT 0 COMMENT 'Is this geometry archived?',
     `archive_date` timestamp NULL  DEFAULT NULL COMMENT 'The Title of this geometry',
     `geometry` geometry    COMMENT 'The Spatial data for this geometry' 
,
        PRIMARY KEY (`id`)
 
    ,     UNIQUE INDEX `index_geometry_type_label`(
        geometry_type_id
      , label
  )
  
,          CONSTRAINT FK_civigeometry_geometry_geometry_type_id FOREIGN KEY (`geometry_type_id`) REFERENCES `civigeometry_geometry_type`(`id`) ON DELETE CASCADE  
)    ;

-- /*******************************************************
-- *
-- * civigeometry_geometry_collection
-- *
-- * Details on a collection of Geometries
-- *
-- *******************************************************/
CREATE TABLE `civigeometry_geometry_collection` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique GeometryCollection ID',
     `geometry_collection_type_id` int unsigned NOT NULL   COMMENT 'FK to civigeomety_geometry_collection_type',
     `label` varchar(255) NOT NULL   COMMENT 'Title of the Geometry Collection',
     `description` varchar(255)   DEFAULT NULL COMMENT 'Description of the Geometry Collection',
     `source` varchar(255)   DEFAULT NULL COMMENT 'Source of the Geometry Collection',
     `is_archive` tinyint   DEFAULT 0 COMMENT 'Is this Geometry Collection archived',
     `archive_date` timestamp NULL  DEFAULT NULL COMMENT 'When was this Geometry Collection archived' 
,
        PRIMARY KEY (`id`)
 
    ,     UNIQUE INDEX `index_type_id_label`(
        geometry_collection_type_id
      , label
  )
  
,          CONSTRAINT FK_civigeometry_geometry_collection_geometry_collection_type_id FOREIGN KEY (`geometry_collection_type_id`) REFERENCES `civigeometry_geometry_collection_type`(`id`) ON DELETE CASCADE  
)    ;

-- /*******************************************************
-- *
-- * civigeometry_geometry_collection_geometry
-- *
-- * Linkage between Geometries and their collections
-- *
-- *******************************************************/
CREATE TABLE `civigeometry_geometry_collection_geometry` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique Geometry ID',
     `geometry_id` int unsigned    COMMENT 'Geometry',
     `geometry_collection_id` int unsigned    COMMENT 'Geometry Collection' 
,
        PRIMARY KEY (`id`)
 
    ,     UNIQUE INDEX `index_geometry_id_geometry_collection_id`(
        geometry_id
      , geometry_collection_id
  )
  
,          CONSTRAINT FK_civigeometry_geometry_collection_geometry_geometry_id FOREIGN KEY (`geometry_id`) REFERENCES `civigeometry_geometry`(`id`) ON DELETE CASCADE,          CONSTRAINT FK_civigeometry_geometry_collection_geometry_geometry_collection_id FOREIGN KEY (`geometry_collection_id`) REFERENCES `civigeometry_geometry_collection`(`id`) ON DELETE CASCADE  
)    ;

 
