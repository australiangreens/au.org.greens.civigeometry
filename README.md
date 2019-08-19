# au.org.greens.civigeometry

This extension allows users to manage geometry spatial data within their CiviCRM instance and allows them to perform calculations between different locations and between locations and polygons.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM (5.7.x)
* MySQL version 5.7 or MariaDB version 10.2

## Installation (Web UI)

This extension has not yet been published for installation via the web UI.

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl au.org.greens.civigeometry@https://github.com.org:australiangreens/au.org.greens.civigeometry/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/australiangreens/au.org.greens.civigeometry.git
cv en civigeometry
```

## Usage

Users can create collections of geometries and can create polygons based on GeoJSON via the CiviCRM API

Avaliable Entities and methods

- GeometryCollection
 -- Create/Update/Delete Collections
 -- Archive/Unarchive - Archive or unarchive a Geometry Collection, doesn't affect the geometries within that collection tho
- GeoemtryType 
  - Useful for specfiying what type of geometry is being stored e.g. Wards, States, Electorates etc
  - CRUD commands supported
- GeometryCollectionType 
  - Useful for specifying the source or type of collection e.g. External, Internal, Ad Hock, User created etc which assists with finding geometries
  - CRUD commands supported
- Geometry
  - CRUD commands supported - When returning Geometry or creating geometry the default format of geometry is that in GeoJSON, you can specify alternate output formats by putting the parameter format in, acceptable output foramts are json = GeoJSON, kml, wkt. 
  You can also specify format when createing a geometry which can be one of 'gzip', 'file'.
  - Archive/UnArchive - Archive or unarchive a perticular geometry
  - getCollections - Find out which colletion ids a geoemtry is in or find out the ids of all the geometries in a specific collection
  - getSpatialData - Return basic spatial data including the geometry envelope, The centroid of the polygon, the SRID of the geometry and is it a simple of complex geometry
  - getBounds - Return the Max, Min Y and X points of a geometry
  - getDistance - get the distance specified between two points. The points need to be specified in string format in the format of `POINT(x, y)`
  - getOverlap - Determine the % overlap between two geometry shapes

## Known Issues

1. When creating a polygon, there can be issues with webserver size limits. If that is an issue, then the best option is to upload the GeoJSON as a file to the CiviCRM Server and then specify the path in the geometry parameter of the Geometry.create call and the `type = 'file'`
2. Calculations on spatial data such as the area of a shape such as using ST_Area do not incorporate SRID concideration as of August 2019 because MySQL and MariaDB do not support it at this stage.
