# Changelog
All notable changes for the CiviGeometry extension will be noted here.

## [1.7.0] - 2020-11-17
### Added
 - Optional expiry date added to geometry-entity relationships
 - API Job to remove expired geometry-entity relationships

## [1.6.0] - 2020-11-10
### Changed
 - Improved performance of contains API, particular if memory is limited some situations
 - Refactored implementation of contains to be easier to follow.

### Fixed
 - Memory exhaustion issue when finding addresses in a geometry. Now uses a generator function
   internally rather than a large array. The refactor also uses a temporary table and is more
   performant.

## [1.5.2] - 2020-09-29
### Added

### Changed
 - Removed overlap cache expiry of 1 month. Overlaps should only expire when the geometries change

### Fixed

## [1.5.1] - 2020-09-23
### Added
 - This changelog.

### Changed
 - Updated to latest version of civix generate version of files
 - Linting improvements

### Fixed
 - The API's getCollection method now correctly references CRM_CiviGeometry_DAO_GeometryCollection_Geometry
 - getGeometryIntersection function renamed
