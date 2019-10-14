# au.org.greens.civigeometry

This extension allows users to manage spatial data within their CiviCRM instance. It presents a number of functions to perform simple spatial operations between different points and between points and polygons.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v5.4+
* CiviCRM (5.13.x)
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

## Configuration

This extension makes use of a queueing system to manage the work of calculating address-geometry relationships. More information is given in the Usage section below.

For this to work you must configure a scheduled job that will be processed by the Civi cron on a regular basis.

First, you must ensure your system has its cron properly setup. Refer to the Scheduled jobs section](https://docs.civicrm.org/sysadmin/en/latest/setup/jobs/) of the CiviCRM System Administrator Guide for further info.

Second, you must create a scheduled job. The [Scheduled jobs section](https://docs.civicrm.org/user/en/latest/initial-set-up/scheduled-jobs/#configuring) of the CiviCRM User Guide has detailed information.

The job details are:
- Entity type: Geometry
- Entity method: runqueue
- no command parameters are required

We recommend configuring the job to run hourly.

## Usage

Users can create collections of geometries and can create polygons based on GeoJSON via the CiviCRM API

Avaliable Entities and methods

- `GeometryCollection`
  - A Geometry Collection is a collation of polygons. For example, a collection of States or Provinces in a country
  - Operations
    - Create/Read/Update/Delete (CRUD). Note that CiviCRM implements these as `create` (Create/Update), `read` and `delete`
    - `archive`/`unarchive` - Archive or unarchive a Geometry Collection. Note: this does not affect geometries linked to a collection
- `GeometryType`
  - Useful for specfiying what type of geometry is being stored e.g. Wards, States, Electorates etc
  - Operations
    - CRUD
- `GeometryCollectionType`
  - Useful for specifying the source or type of collection e.g. External, Internal, Ad-Hoc, User created, etc., which can assist with finding geometries
  - Operations
    - CRUD
- `Geometry`
  - A Geometry is a polygon that defines an enclosed spatial region. For example, state or province boundaries, council areas, electorates, etc.
  - To create a Geometry you need to specify at least one collection and you need to pass it in an array e.g. `collection_id => [1]`
  - Operations
    - CRUD
      - When requesting or creating geometry the default format is GeoJSON. You can specify alternate output formats via the parameter format. Acceptable output formats are json (ie. GeoJSON), kml and wkt. 
      - When you create a geometry a job is added to the queue to generate all the relationships between geocoded addresses (i.e. have a latitude and longditude) and the geometry polygon.
      - You can also specify an input format when creating a geometry. GeoJSOa (default), gzipped GeoJSON (`gzip`) and (server-side) file references (`file`) are acceptable input formats.
    - `archive`/`unarchive` - Archive or unarchive a single geometry
    - `getCollections` - Find out which collection a geometry belongs to, or find out the ids of all the geometries in a specific collection
    - `getSpatialData` - Return basic spatial data including the envelope and centroid of the polygon, the SRID of the polygon (see Known Issue #2 below), and whether it is a simple or complex geometry (polygons are always considered complex)
    - `getBounds` - Return the min/max X and Y points of a geometry
    - `getDistance` - Return the distance specified between two points. The points need to be specified in string format in the format of `POINT(x, y)`
    - `getOverlap` - Determine the overlap between two geometry shapes. Returned as a percentage
    - `runqueue` - Runs the queued up address placement and geometry - address relationship creation jobs stored in the Geometry extension queue. 
- `Address`
  - Operations
    - `creategeometries` - add relationship records between an address and a geometry into the `civigeometry_address_geometry` table
    - `getgeometries` - get all geometries for a specific address or get all the addresses that are within a specific geometry.

When an address is created in the system and it has been sucessfully geocoded there will be a job added to the queue created by the extension to then calculate what geometries is the lat and long point of the address within and store those relationships via the `address.creategeometries` API method.

## Known Issues

1. When creating a polygon, there can be issues with webserver size limits. If that is an issue, then the best option is to upload the GeoJSON as a file to the CiviCRM server and specify the path in the geometry parameter of the Geometry.create call and the `type = 'file'`
2. As of August 2019, MySQL and MariaDB do not use SRID values when performing spatial operations such as `ST_Area`. These operations default to a SRID value of 4326. This produces different results to what one might expect using the same operation in postgreSQL/postGIS.
