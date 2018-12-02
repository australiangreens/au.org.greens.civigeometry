# au.org.greens.civigeometry

![Screenshot](/images/screenshot.png)

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
cv dl au.org.greens.civigeometry@https://bitbucket.org:australian_greens/au.org.greens.civigeometry/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://bitbucket.org/australian_greens/au.org.greens.civigeometry.git
cv en civigeometry
```

## Usage

Users can create collections of geometries and can create polygons based on GeoJSON via the CiviCRM API

## Known Issues

When creating a polygon, there can be issues with webserver size limits. If that is an issue, then the best option is to upload the GeoJSON as a file to the CiviCRM Server and then specify the path in the geometry parameter of the Geometry.create call and the `type = 'file'`
