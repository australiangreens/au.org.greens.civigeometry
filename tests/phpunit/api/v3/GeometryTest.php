<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;

/**
 * This test class tests creating and returning Geometries and also geometry information such as overlap, point to geometry and testing if a point is in a geometry.
 *
 * Tips:
 *  - With HookInterface, you may implement CiviCRM hooks directly in the test class.
 *    Simply create corresponding functions (e.g. "hook_civicrm_post(...)" or similar).
 *  - With TransactionalInterface, any data changes made by setUp() or test****() functions will
 *    rollback automatically -- as long as you don't manipulate schema or truncate tables.
 *    If this test needs to manipulate schema or truncate tables, then either:
 *       a. Do all that using setupHeadless() and Civi\Test.
 *       b. Disable TransactionalInterface, and handle all setup/teardown yourself.
 *
 * @group headless
 */
class api_v3_GeometryTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface {

  use \Civi\Test\Api3DocTrait;
  use \Civi\Test\GenericAssertionsTrait;
  use \Civi\Test\ContactTestTrait;

  private $jsonDirectoryStore = __DIR__ . DIRECTORY_SEPARATOR . 'load';

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    // Create a collection type for external collections
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $this->externalCollectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    // Create a collection for states
    $collectionParams = [
      'label' => 'States',
      'source' => 'ABS',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $this->statesCollection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create a geometry type for state geometries
    $geometryTypeParams = [
      'label' => 'State',
    ];
    $this->stateGeometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    // Create a collection for SA1 Geometries
    $sa1CollectionParams = [
      'label' => 'SA1',
      'source' => 'ABS',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $this->sa1Collection = $this->callAPISuccess('GeometryCollection', 'create', $sa1CollectionParams);
    // Create a Geometry Type for SA1 Geometry
    $sa1GeometryTypeParams = [
      'label' => 'Stastical Area Level 1',
    ];
    $this->sa1GeometryType = $this->callAPISuccess('GeometryType', 'create', $sa1GeometryTypeParams);
    parent::setUp();
  }

  public function tearDown() {
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $this->stateGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $this->statesCollection['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $this->sa1GeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $this->sa1Collection['id']]);
    $this->callAPISuccess('GeometryCollectionType', 'delete', ['id' => $this->externalCollectionType['id']]);  
    parent::tearDown();
  }

  /**
   * Test Creating a geometry in the database.
   * Ensure that we can handle passing an array of collection ids and that we require at least one collection, a geometry type and that the geometry is specified.
   */
  public function testCreateGeometry() {
    // Load geoJSON file and create a geometry
    $sa1JSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_sa1_geometry.json');
    // Create SA1 Geometry
    $sa1 = $this->callAPISuccess('Geometry', 'create', [
      'label' => '1210113836',
      'geometry_type_id' => $this->sa1GeometryType['id'],
      'collection_id' => [$this->sa1Collection['id']],
      'geometry' => trim($sa1JSON),
    ]);
    // RULE: A Geometry can be assigned to one or more collections but never 0
    $geometryCollectionCount = $this->callAPISuccess('Geometry', 'getCollection', ['geometry_id' => $sa1['id']]);
    $this->assertEquals(1, $geometryCollectionCount['count']);
    $this->assertEquals($this->sa1Collection['id'], $geometryCollectionCount['values'][$geometryCollectionCount['id']]['collection_id']);
    // Check that the returned geometry matches what was set to be stored. use json_decode function to convert to an array, so that white space is not an issue
    $this->assertEquals(json_decode($sa1JSON, TRUE), json_decode($sa1['values'][$sa1['id']]['geometry'], TRUE));
    $this->assertEquals(json_decode($sa1JSON, TRUE), json_decode($this->callAPISuccess('Geometry', 'get', ['id' => $sa1['id']])['values'][$sa1['id']]['geometry'], TRUE));
    // RULE: Geometries can only have 1 type associated.
    $geometryType2 = $this->callAPISuccess('GeometryType', 'Create', ['label' => 'Australian States']);
    $geometryParams['geometry_type_id'] = "{$geometryType2['id']}, {$this->sa1Collection['id']}";
    $this->callAPIFailure('Geometry', 'create', $geometryParams);
    $geometryParams['geometry_type_id'] = [$geometryType2['id'], $this->sa1Collection['id']];
    $this->callAPIFailure('Geometry', 'create', $geometryParams);
    $queenslandJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'queensland.json');
    $geometryParams = [
      'label' => 'Queensland',
      'geometry_type_id' => $this->stateGeometryType['id'],
      // collection_id accepts an array of ids or a comma separated list of ids.
      'collection_id' => [$this->statesCollection['id']],
      'geometry' => $queenslandJSON,
    ];
    $queensland = $this->callAPISuccess('Geometry', 'create', $geometryParams);
    $collectionGet = $this->callAPISuccess('Geometry', 'get', ['collection_id' => $this->statesCollection['id']]);
    // We should find 1 geometry
    $this->assertEquals(1, $collectionGet['count']);
    // Check that if we pass in 2 geometry collection types then it will return both the SA1 and the Queensland Geometry
    $collectionGet2 = $this->callAPISuccess('Geometry', 'get', ['collection_id' => ['IN' => [$this->statesCollection['id'], $this->sa1Collection['id']]]]);
    $this->assertEquals(2, $collectionGet2['count']);
    // Tear down test data
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType2['id']]);
    // verify that we can delete geometries as well as archiving them.
    $this->callAPISuccess('Geometry', 'delete', ['id' => $queensland['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $sa1['id']]);
  }

  /**
   * Test that the gZip libary works
   */
  public function testGzipExtension() {
    $gziped = gzencode('hello');
    $this->assertEquals('hello', gzdecode($gziped));
  }

  /**
   * Test creating geometry using gzip data
   */
  public function testCreateGzipedGeometry() {
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_sa1_geometry.json');
    $geometryJSONForGzipping = str_replace('"', "'", $geometryJSON);
    $gzipedGeometryJSON = gzencode($geometryJSONForGzipping);
    // Create Geometry specifying that the format is gzip
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'sample_sa1_geometry',
      'geometry_type_id' => $this->sa1GeometryType['id'],
      'collection_id' => [$this->sa1Collection['id']],
      'geometry' => $gzipedGeometryJSON,
      'format' => 'gzip',
    ]);
    // RULE: A Geometry can be assigned to one or more collections but never 0
    $collectionsGeometryisIn = $this->callAPISuccess('Geometry', 'getCollection', ['geometry_id' => $geometry['id']]);
    // Assert that the GeoJSON has been stored correctly in the database. use json_decode to avoid any whitespace issues
    $this->assertEquals(json_decode($geometryJSON, TRUE), json_decode($geometry['values'][$geometry['id']]['geometry'], TRUE));
    $this->assertEquals(json_decode($geometryJSON, TRUE), json_decode($this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id']])['values'][$geometry['id']]['geometry'], TRUE));
    $this->assertEquals(1, $collectionsGeometryisIn['count']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $geometry['id']]);
  }

  /**
   * Test creating geometry using a specified file as the geometry.
   */
  public function testCreateGeometryFromFile() {
    // Create a collection
    $collectionParams = [
      'label' => 'NSW Branches',
      'source' => 'Greens NSW',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $NSWBranchesCollection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create a geometry type
    $geometryTypeParams = [
      'label' => 'Branch',
    ];
    $branchGeometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryFile = \CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_nsw_branch_geometry.json';
    // Create Geometry specifying file as the format
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample NSW Branch Geometry',
      'geometry_type_id' => $branchGeometryType['id'],
      'collection_id' => [$NSWBranchesCollection['id']],
      'geometry' => $geometryFile,
      'format' => 'file',
    ]);
    // Check that the geometry created matches that in the file.
    $this->assertEquals(json_decode(file_get_contents($geometryFile), TRUE), json_decode($geometry['values'][$geometry['id']]['geometry'], TRUE));
    $this->callAPISuccess('Geometry', 'delete', ['id' => $geometry['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $branchGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $NSWBranchesCollection['id']]);
  }

  /**
   * Verify that MySQL/MariaDB is not using the Minimum Bounding Rectangle rather using the actual geometry
   * when determining if a point is withing the geometry
   */
  public function testMySQLSTContains() {
    // Create a collection
    $UHCollectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $UHCollection = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    $UHCollectionParams['label'] = 'Tasmanian Upper House No MBR';
    $UHCollection2 = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    // Create a geometry type
    $UHGometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $UHGeometryType = $this->callAPISuccess('GeometryType', 'create', $UHGometryTypeParams);
    // upperHouseDistrict is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    // We are going to create 2 geometry records 1 being the geometry itself and the other being the MBR of the upperHouseDistrict Geometry
    $upperHouseDistrictJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_tasmanian_upper_house_geometry.json');
    $upperHouseDistrict = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House ',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => [$UHCollection['id'], $UHCollection2['id']],
      'geometry' => trim($upperHouseDistrictJSON),
    ]);
    $upperHouseDistrictMBRData = CRM_Core_DAO::singleValueQuery("SELECT ST_AsGeoJSON(ST_Envelope(geometry)) FROM civigeometry_geometry where id = %1", [1 => [$upperHouseDistrict['id'], 'Positive']]);
    $upperHouseDistrictMBR = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House  MBR',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => [$UHCollection['id']],
      'geometry' => trim($upperHouseDistrictMBRData),
    ]);
    // Prove that this address is within upperHouseDistrict
    $individualResult = $this->callAPISuccess('Geometry', 'contains', [
      'geometry_a' => $upperHouseDistrict['id'],
      'geometry_b' => 'POINT(147.2687833 -42.9771098)',
    ]);
    $this->assertEquals(1, $individualResult['values']);
    // Prove that a point that is within the MBR but not the actual geometry returns 0 for an ST_contains on the actual geometry (test RMDS isn't using MBR to do the ST_Contains).
    $nonMBRResult = $this->callAPISuccess('geometry', 'contains', [
      'geometry_a' => $upperHouseDistrict['id'],
      'geometry_b' => 'POINT(147.243 -42.983)',
    ]);
    $this->assertEquals(0, $nonMBRResult['values']);
    // Prove that a point that is within the MBR but not the actual geometry returns 0 for an ST_contains on the MBR geometry
    $mbrResult = $this->callAPISuccess('geometry', 'contains', [
      'geometry_a' => $upperHouseDistrictMBR['id'],
      'geometry_b' => 'POINT(147.243 -42.983)',
    ]);
    $this->assertEquals(1, $mbrResult['values']);
    // Test that when no geometry is specified that this point is found in both the original poly and the MBR geometry
    $results = $this->callAPISuccess('Geometry', 'contains', [
      'geometry_a' => 0,
      'geometry_b' => 'POINT(147.2687833 -42.9771098)',
    ]);
    $this->assertEquals(2, $results['count']);
    $this->assertContains($upperHouseDistrict['id'], $results['values']);
    $this->assertContains($upperHouseDistrictMBR['id'], $results['values']);
    // Check that when we specify a collection that only contains the non MBR geometry that that is the only geometry returned
    $resultWithCollection = $this->callAPISuccess('Geometry', 'contains', [
      'geometry_a' => 0,
      'geometry_a_collection_id' => $UHCollection2['id'],
      'geometry_b' => 'POINT(147.2687833 -42.9771098)',
    ]);
    $this->assertEquals(1, $resultWithCollection['count']);
    $this->assertContains($upperHouseDistrict['id'], $resultWithCollection['values']);
    // Assert that the non MBR geometry contains its self and MBR
    $resultGeometryIdB = $this->callAPISuccess('Geometry', 'contains', [
      'geometry_a' => 0,
      'geometry_b' => $upperHouseDistrict['id'],
    ]);
    $this->assertEquals(2, $resultGeometryIdB['count']);
    $this->assertContains($upperHouseDistrict['id'], $resultGeometryIdB['values']);
    $this->assertContains($upperHouseDistrictMBR['id'], $resultGeometryIdB['values']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrict['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrictMBR['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $UHGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $UHCollection['id']]);
  }

  /**
   * Test removing collection fails when geometry only belongs in one collection.
   * This is expected to fail as geometry has to be in a collection.
   */
  public function testRemoveOnlyCollection() {
    $UHCollectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $UHCollection = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    $UHGeometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $UHGeometryType = $this->callAPISuccess('GeometryType', 'create', $UHGeometryTypeParams);
    // Our sample geometry is of Nelson is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    $upperHouseDistrictJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_tasmanian_upper_house_geometry.json');
    $upperHouseDistrict = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House ',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => [$UHCollection['id']],
      'geometry' => trim($upperHouseDistrictJSON),
    ]);
    // RULE Geometries must be in at least one collection.
    $this->callAPIFailure('Geometry', 'removecollection', [
      'geometry_id' => $upperHouseDistrict['id'],
      'collection_id' => [$UHCollection['id']],
    ]);
    // tear down created objects
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrict['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $UHGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $UHCollection['id']]);
  }

  /**
   * Remove a Geometry from an collection when the Geometry is in Multiple collections.
   */
  public function testRemoveCollection() {
    // Create Upper house Geometry
    $collectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create a superset of the first collection
    $collection2 = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'Upper House Districts',
      'source' => 'Electoral Commissions',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ]);
    // Create Geometry Type
    $geometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $upperHouseDistrictJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_tasmanian_upper_house_geometry.json');
    // Permit adding multiple collections when creating the geometry
    $upperHouseDistrict = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House ',
      'geometry_type_id' => $geometryType['id'],
      // collection_id accepts an array of ids or a comma separated list of ids.
      'collection_id' => "{$collection['id']}, {$collection2['id']}",
      'geometry' => trim($upperHouseDistrictJSON),
    ]);
    // Remove Geometry from the 2nd collection created
    // RULE Geometries can be in more than one collection but have to be in at least 1 collection.
    $this->callAPISuccess('Geometry', 'removecollection', [
      'geometry_id' => $upperHouseDistrict['id'],
      'collection_id' => [$collection2['id']],
    ]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrict['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection2['id']]);
  }

  /**
   * Test get Geometry Centroid.
   */
  public function testGetGeometryCentroid() {
    // Create Upper House Geometry Collection
    $collectionParams = [
      'label' => 'Tas Upper House Districts',
      'source' => 'TASEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create Geometry Type
    $geometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $upperHouseDistrictJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_tasmanian_upper_house_geometry.json');
    // Create Geometry
    $upperHouseDistrict = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House ',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => trim($upperHouseDistrictJSON),
    ]);
    $centroid = $this->callAPISuccess('Geometry', 'getcentroid', ['id' => $upperHouseDistrict['id']]);
    // Check that the expected points can be found in the array. MariaDB and MySQL each print the array in a different order.
    $this->assertContains('147.29234219', $centroid['values']);
    $this->assertContains('-42.94807285', $centroid['values']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrict['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection['id']]);
  }

  /**
   * Test Archiving a Geometry
   */
  public function testArchiveGeometry() {
    // Create Collection for archiving geometry
    $collectionParams = [
      'label' => 'Queensland Wards',
      'source' => 'QEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create Geometry Type
    $geometryTypeParams = [
      'label' => 'LGA Wards',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_qld_ward_geometry.json');
    // Create Ward Geometry
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Queensland Ward',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => $geometryJSON,
    ]);
    $this->callAPISuccess('Geometry', 'archive', ['id' => $geometry['id']]);
    $geometry = $this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id']]);
    $this->assertEquals(date('Y-m-d h:i:s'), $geometry['values'][$geometry['id']]['archived_date']);
    $this->assertEquals(1, $geometry['values'][$geometry['id']]['is_archived']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $geometry['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection['id']]);
  }

  /**
   * Test Unarchiving a Geometry.
   */
  public function testUnArchiveGeometry() {
    // Create a collection for our wards
    $collectionParams = [
      'label' => 'Queensland Wards',
      'source' => 'QEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create Geometry Type
    $geometryTypeParams = [
      'label' => 'LGA Wards',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_qld_ward_geometry.json');
    // Create Ward Geometry
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Queensland Ward',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => $geometryJSON,
    ]);
    // RULE can only unarchive archived geometries
    $this->callAPIFailure('Geometry', 'unarchive', ['id' => $geometry['id']]);
    $this->callAPISuccess('Geometry', 'archive', ['id' => $geometry['id']]);
    $geometry = $this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id']]);
    // Check that archived_date is properly set
    $this->assertEquals(date('Y-m-d h:i:s'), $geometry['values'][$geometry['id']]['archived_date']);
    $this->assertEquals(1, $geometry['values'][$geometry['id']]['is_archived']);
    $this->callAPISuccess('Geometry', 'unarchive', ['id' => $geometry['id']]);
    $geometry = $this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id']]);
    // Check that the archived date
    $this->assertEquals(0, $geometry['values'][$geometry['id']]['is_archived']);
    $this->assertFalse(isset($geometry['values'][$geometry['id']]['archived_date']));
    $this->callAPISuccess('Geometry', 'delete', ['id' => $geometry['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection['id']]);
  }

  /**
   * Test get intersection
   */
  public function testGetIntersection() {
    $queenslandJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'queensland.json');
    // Create Queensland state geometry
    $queensland = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Queensland',
      'geometry_type_id' => $this->stateGeometryType['id'],
      'collection_id' => [$this->statesCollection['id']],
      'geometry' => trim($queenslandJSON),
    ]);
    // Create Collection for QLD Wards
    $wardsCollection = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'Queensland Wards',
      'source' => 'QLD',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ]);
    // Greate Geometry try
    $wardGeometryType = $this->callAPISuccess('GeometryType', 'create', [
      'label' => 'LGA Wards',
    ]);
    $cairnsJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_qld_ward_geometry.json');
    // Create ward Geometry
    $cairns = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Queensland Ward',
      'geometry_type_id' => $wardGeometryType['id'],
      'collection_id' => [$wardsCollection['id']],
      'geometry' => trim($cairnsJSON),
    ]);
    $result = $this->callAPISuccess('Geometry', 'getIntersection', [
      'geometry_a' => $cairns['id'],
      'collection_id' => $this->statesCollection['id'],
    ]);
    $this->assertEquals(['geometry_a' => $cairns['id'], 'geometry_b' => $queensland['id']], $result['values'][0]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $cairns['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $queensland['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $wardGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $wardsCollection['id']]);
  }

  /**
   * Test Generating an Overlap Cache.
   */
  public function testOverlapGenerationCache() {
    $queenslandJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'queensland.json');
    // Create Queensland state geometry
    $queensland = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Queensland',
      'geometry_type_id' => $this->stateGeometryType['id'],
      'collection_id' => [$this->statesCollection['id']],
      'geometry' => trim($queenslandJSON),
    ]);
    // Create Collection for QLD Wards
    $wardsCollection = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'Queensland Wards',
      'source' => 'QLD',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ]);
    // Greate Geometry try
    $wardGeometryType = $this->callAPISuccess('GeometryType', 'create', [
      'label' => 'LGA Wards',
    ]);
    $cairnsJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_qld_ward_geometry.json');
    // Create ward Geometry
    $cairns = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Queensland Ward',
      'geometry_type_id' => $wardGeometryType['id'],
      'collection_id' => [$wardsCollection['id']],
      'geometry' => trim($cairnsJSON),
    ]);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $cairns['id'],
      'geometry_id_b' => $queensland['id'],
    ]);
    // Check that Sample Queensland Ward only covers 4% of Queensland state.
    $this->assertEquals(4, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertFalse($overlap['values'][$overlap['id']]['cache_used']);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $cairns['id'],
      'geometry_id_b' => $queensland['id'],
    ]);
    // Verify calling the API again gets the same result and the cache has been used.
    $this->assertEquals(4, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertTrue($overlap['values'][$overlap['id']]['cache_used']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $cairns['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $queensland['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $wardGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $wardsCollection['id']]);
  }

  /**
   * Test Generating an Overlap between 2 specific geometries is within 97 and 100%
   */
  public function testOverlapGeneration() {
    $sa1JSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_sa1_geometry.json');
    // Create SA1 Geometry
    $sa1 = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'sample_sa1_geometry',
      'geometry_type_id' => $this->sa1GeometryType['id'],
      'collection_id' => [$this->sa1Collection['id']],
      'geometry' => trim($sa1JSON),
    ]);
    // Create a collection for our wards
    $wardsCollection = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'NSW Wards',
      'source' => 'NSW',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ]);
    // Create a geometry Type
    $wardGeometryType = $this->callAPISuccess('GeometryType', 'create', [
      'label' => 'LGA Wards',
    ]);
    $nswWardJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_nsw_ward_geometry.json');
    // Create Ward Geometry
    $nswWard = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample NSW Ward',
      'geometry_type_id' => $wardGeometryType['id'],
      'collection_id' => [$wardsCollection['id']],
      'geometry' => trim($nswWardJSON),
    ]);
    // Get the overlap between the SA1 as inner of the Naremburn wardd
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $sa1['id'],
      'geometry_id_b' => $nswWard['id'],
    ]);
    // MariaDB and MySQL will do these calculations slightly differently but it should be in a 3% range between 100% and 97%.
    $this->assertGreaterThan(97, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertLessThanOrEqual(100, $overlap['values'][$overlap['id']]['overlap']);
    // Assert that the cache is not warmed up at all
    $this->assertFalse($overlap['values'][$overlap['id']]['cache_used']);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $sa1['id'],
      'geometry_id_b' => $nswWard['id'],
    ]);
    // Check that the result came from the cache for performance reasons.
    $this->assertTrue($overlap['values'][$overlap['id']]['cache_used']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $sa1['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $nswWard['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $wardGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $wardsCollection['id']]);
  }

  /**
   * Test Generating an Overlap between 2 specific geometries is within 97 and 100%
   */
  public function test0OverlapGeneration() {
    $sa1JSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_sa1_geometry.json');
    // Create SA1 Geometry
    $sa1 = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'sample_sa1_geometry',
      'geometry_type_id' => $this->sa1GeometryType['id'],
      'collection_id' => [$this->sa1Collection['id']],
      'geometry' => trim($sa1JSON),
    ]);
    // Create Collection for QLD Wards
    $wardsCollection = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'Queensland Wards',
      'source' => 'QLD',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ]);
    // Greate Geometry try
    $wardGeometryType = $this->callAPISuccess('GeometryType', 'create', [
      'label' => 'LGA Wards',
    ]);
    $cairnsJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_qld_ward_geometry.json');
    // Create ward Geometry
    $cairns = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Queensland Ward',
      'geometry_type_id' => $wardGeometryType['id'],
      'collection_id' => [$wardsCollection['id']],
      'geometry' => trim($cairnsJSON),
    ]);
    $overlapResult = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $sa1['id'],
      'geometry_id_b' => $cairns['id'],
    ]);
    $this->assertEquals(0, $overlapResult['values'][$overlapResult['id']]['overlap']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $cairns['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $sa1['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $wardGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $wardsCollection['id']]);
  }

  /**
   * Test getting a distance
   * @note Postgres reported 2,202 metres here however MySQL5.7 using native functions returned 2,197
   */
  public function testGetDistance() {
    $result = $this->callAPISuccess('Geometry', 'getdistance', [
      'geometry_a' => 'POINT(147.2687833 -42.9771098)',
      'geometry_b' => 'POINT(147.243 -42.983)',
    ]);
    $this->assertEquals('2197', (int) $result['values']);
  }

  /**
   * Test returning spatial properties for a geometry
   */
  public function testSpatialDataProperties() {
    // Create a collection
    $collectionParams = [
      'label' => 'NSW Branches',
      'source' => 'Greens NSW',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $NSWBranchesCollection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create a geometry type
    $geometryTypeParams = [
      'label' => 'Branch',
    ];
    $branchGeometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryFile = \CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_nsw_branch_geometry.json';
    // Create Geometry
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample NSW Branch Geometry',
      'geometry_type_id' => $branchGeometryType['id'],
      'collection_id' => [$NSWBranchesCollection['id']],
      'geometry' => $geometryFile,
      'format' => 'file',
    ]);
    $spatialData = $this->callAPISuccess('Geometry', 'getspatialdata', ['id' => $geometry['id']]);
    // Test Accuracy of spatical data.
    // The envelopeBounds come from PostgresSQL instance
    $envelopeBounds = explode(',', '151.126707616 -33.853568996,151.126707616 -33.778527002,151.268936992 -33.778527002,151.268936992 -33.853568996,151.126707616 -33.853568996');
    $envelopeBoundsFromDB = explode(',', substr($spatialData['values'][$geometry['id']]['ST_Envelope'], 9, -2));
    // For Some reason the 2nd and 4th set of bounds are orded differently depending on PostGres or MySQL/MariaDB
    $this->assertEquals($envelopeBounds[0], $envelopeBoundsFromDB[0]);
    $this->assertEquals($envelopeBounds[3], $envelopeBoundsFromDB[1]);
    $this->assertEquals($envelopeBounds[2], $envelopeBoundsFromDB[2]);
    $this->assertEquals($envelopeBounds[1], $envelopeBoundsFromDB[3]);
    $this->assertEquals($envelopeBounds[4], $envelopeBoundsFromDB[4]);
    $centriodInformation = explode(' ', substr($spatialData['values'][$geometry['id']]['ST_Centroid'], 6, -1));
    $this->assertEquals('151.195994', substr($centriodInformation[0], 0, 10));
    $this->assertEquals('-33.8176881', substr($centriodInformation[1], 0, 11));
    // PostGres reported area of 57.749 Square KM
    // MariaDB and MySQL 5.7 reported 56.236
    // Note the difference is that the Postgres query worked on the Goemetry Plain SRID 4326 where as at present
    // MySQL and MariaDB work on just SRID of 0 for their ST_area calculations
    $this->assertEquals('56.236', $spatialData['values'][$geometry['id']]['square_km']);
    $bounds = $this->callAPISuccess('Geometry', 'getbounds', ['id' => $geometry['id']]);
    $this->assertEquals(['left_bound' => '151.126707616', 'bottom_bound' => '-33.853568996', 'top_bound' => '-33.778527002', 'right_bound' => '151.268936992'], $bounds['values'][$geometry['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $geometry['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $branchGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $NSWBranchesCollection['id']]);
  }

  /**
   * Test returning geometry in KML format
   */
  public function testCustomOutputFormat() {
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_sa1_geometry.json');
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'sample_sa1_geometry',
      'geometry_type_id' => $this->sa1GeometryType['id'],
      'collection_id' => [$this->sa1Collection['id']],
      'geometry' => $geometryJSON,
    ]);
    $getGeometry = $this->callAPISuccess('Geometry', 'get', ['format' => 'kml']);
    $this->assertEquals('<MultiGeometry><Polygon><outerBoundaryIs><LinearRing><coordinates>151.18540272,-33.8022812055 151.185615104,-33.8022131255 151.186521952,-33.802339499 151.18660944,-33.8023522825 151.186400992,-33.8033745925 151.18620336,-33.8043426235 151.185657952,-33.8071025645 151.184443328,-33.806932827 151.184176192,-33.8065670635 151.183489312,-33.807019851 151.183223648,-33.807194824 151.18304544,-33.806998983 151.18288128,-33.8068107085 151.182722176,-33.806619178 151.18257024,-33.8064241509999 151.18244352,-33.8062482715 151.182426976,-33.8062253315 151.182289088,-33.806014191 151.182224928,-33.805905744 151.18216128,-33.8057982035 151.182068416,-33.8056292245 151.182040672,-33.805578775 151.18192512,-33.805357478 151.181736832,-33.8049863125 151.181663776,-33.8048362775001 151.181523008,-33.804534339 151.18145568,-33.804382491 151.181874016,-33.8041690195 151.18220064,-33.803836038 151.1829096,-33.8033327825 151.183241152,-33.8031041595 151.18357392,-33.8028830105 151.183727808,-33.802830822 151.184271168,-33.8026439905 151.18540272,-33.8022812055 </coordinates></LinearRing></outerBoundaryIs></Polygon></MultiGeometry>',
      $getGeometry['values'][$getGeometry['id']]['geometry']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $geometry['id']]);
  }

  /**
   * Test that requesting only specific parameters you only get those paremters back
   */
  public function testGeometryReturnParams() {
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_sa1_geometry.json');
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'sample_sa1_geometry',
      'geometry_type_id' => $this->sa1GeometryType['id'],
      'collection_id' => [$this->sa1Collection['id']],
      'geometry' => $geometryJSON,
    ]);
    $geometryGet = $this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id'], 'return' => ['id', 'label', 'geometry_type_id']]);
    $this->assertEquals('sample_sa1_geometry', $geometryGet['values'][$geometry['id']]['label']);
    $this->assertEquals($geometry['id'], $geometryGet['values'][$geometry['id']]['id']);
    $this->assertEquals($this->sa1GeometryType['id'], $geometryGet['values'][$geometry['id']]['geometry_type_id']);
    // Assert that we haven't returned geometry.
    $this->assertTRUE(!isset($geometryGet['values'][$geometry['id']]['geometry']));
    // Assert that when we request geometry we get it back
    $geometryGet2 = $this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id'], 'return' => ['geometry']]);
    $this->assertEquals(json_decode($geometryJSON, TRUE), json_decode($geometryGet2['values'][$geometry['id']]['geometry'], TRUE));
    $this->callAPISuccess('Geometry', 'delete', ['id' => $geometry['id']]);
  }

  /**
   * Verify that MySQL/MariaDB is not using the Minimum Bounding Rectangle rather using the actual geometry
   * when determining if a point is withing the geometry
   */
  public function testGeometryAddressStorage() {
    // Create a collection
    $UHCollectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $UHCollection = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    // Create a geometry type
    $UHGometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $UHGeometryType = $this->callAPISuccess('GeometryType', 'create', $UHGometryTypeParams);
    // upperHouseDistrict is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    // We are going to create 2 geometry records 1 being the geometry itself and the other being the MBR of the upperHouseDistrict Geometry
    $upperHouseDistrictJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_tasmanian_upper_house_geometry.json');
    $upperHouseDistrict = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => $UHCollection['id'],
      'geometry' => trim($upperHouseDistrictJSON),
    ]);
    // Create a CiviCRM Address with a known point
    $contact = $this->individualCreate();
    $address = $this->callAPISuccess('Address', 'Create', [
      'contact_id' => $contact,
      'location_type_id' => 'Billing',
      'street_address' => '123 Happy Place',
      'city' => 'New York',
      'skip_geocode' => 1,
      'geo_code_1' => '-42.9771098',
      'geo_code_2' => '147.2687833',
    ]);
    // Return Geometry IDs for which this point from the address is in
    $result = $this->callAPISuccess('Geometry', 'contains', [
      'geometry_a' => 0,
      'geometry_b' => 'POINT(' . $address['values'][$address['id']]['geo_code_2'] . ' ' . $address['values'][$address['id']]['geo_code_1'] . ')',
    ]);
    // Process The Geometry Queue.
    $this->callAPISuccess('Geometry', 'runqueue', []);
    $getResult = $this->callAPISuccess('Address', 'getgeometries', [
      'address_id' => $address['id'],
    ]);
    $this->assertEquals(1, $getResult['count']);
    $this->assertEquals(array_values($result['values']), array_values(CRM_Utils_Array::collect('geometry_id', $getResult['values'])));
    // Assert That when we skip the cache we get the same information back.
    $nonCacheResult = $this->callAPISuccess('Address', 'getgeometries', [
      'address_id' => $address['id'],
      'skip_cache' => 1,
    ]);
    $this->assertEquals(array_values($result['values']), array_values(CRM_Utils_Array::collect('geometry_id', $nonCacheResult['values'])));
    $this->callAPISuccess('Address', 'delete', ['id' => $address['id'], 'skip_undelete' => 1]);
    $this->callAPISuccess('Contact', 'delete', ['id' => $contact, 'skip_undelete' => 1]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrict['id'], 'skip_undelete' => 1]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $UHGeometryType['id'], 'skip_undelete' => 1]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $UHCollection['id'], 'skip_undelete' => 1]);
  }

  /**
   * Test being able return a list of Adddresses for a Geometry
   */
  public function testgetAddressGeometry() {
    $timestart = microtime(TRUE);
    // Create a collection
    $UHCollectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $UHCollection = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    $UHCollectionParams['label'] = 'Tasmanian Upper House No MBR';
    $UHCollection2 = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    // Create a geometry type
    $UHGometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $UHGeometryType = $this->callAPISuccess('GeometryType', 'create', $UHGometryTypeParams);
    // upperHouseDistrict is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    // We are going to create 2 geometry records 1 being the geometry itself and the other being the MBR of the upperHouseDistrict Geometry
    $upperHouseDistrictJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_tasmanian_upper_house_geometry.json');
    $upperHouseDistrict = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House ',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => [$UHCollection['id'], $UHCollection2['id']],
      'geometry' => trim($upperHouseDistrictJSON),
    ]);
    // Create a CiviCRM Address with a known point
    $contact = $this->individualCreate();
    $address = $this->callAPISuccess('Address', 'Create', [
      'contact_id' => $contact,
      'location_type_id' => 'Billing',
      'street_address' => '123 Happy Place',
      'city' => 'New York',
      'skip_geocode' => 1,
      'geo_code_1' => '-42.9771098',
      'geo_code_2' => '147.2687833',
    ]);
    // Process The Geometry Queue.
    $this->callAPISuccess('Geometry', 'runqueue', []);
    // Return address IDs for which are in this specific geometry
    $result = $this->callAPISuccess('Address', 'getgeometries', ['geometry_id' => $upperHouseDistrict['id']]);
    $entityResult = $this->callAPISuccess('geometry', 'getentity', [
      'entity_id' => $address['id'],
      'entity_table' => 'civicrm_address',
    ]);
    $this->assertEquals($address['id'], $result['values'][$result['id']]['entity_id']);
    $this->assertEquals($upperHouseDistrict['id'], $entityResult['values'][$entityResult['id']]['geometry_id']);
    // Ensure that all records for a geometry are removed when it is archived
    $this->callAPISuccess('geometry', 'archive', ['id' => $upperHouseDistrict['id']]);
    $result2 = $this->callAPISuccess('Address', 'getgeometries', ['geometry_id' => $upperHouseDistrict['id']]);
    $this->assertEquals(0, $result2['count']);
    // Esure that when we pass skip cache that we still return information back even if the geometry is archived.
    $nonCacheResult = $this->callAPISuccess('Address', 'getgeometries', ['geometry_id' => $upperHouseDistrict['id'], 'skip_cache' => 1]);
    $this->assertEquals(1, $nonCacheResult['count']);
    $this->assertEquals($upperHouseDistrict['id'], $nonCacheResult['values'][0]['geometry_id']);
    $this->callAPISuccess('Address', 'delete', ['id' => $address['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrict['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $UHGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $UHCollection2['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $UHCollection['id']]);
  }

  /**
   * Test being able return a list of Addresses for a Geometry
   */
  public function testAddressGeometryCacheisUpdatedAfterGeometryisCreated() {
    // Create a collection
    $UHCollectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $UHCollection = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    $UHCollectionParams['label'] = 'Tasmanian Upper House No MBR';
    $UHCollection2 = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    // Create a geometry type
    $UHGometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $UHGeometryType = $this->callAPISuccess('GeometryType', 'create', $UHGometryTypeParams);
    // Create a CiviCRM Address with a known point
    $contact = $this->individualCreate();
    $address = $this->callAPISuccess('Address', 'Create', [
      'contact_id' => $contact,
      'location_type_id' => 'Billing',
      'street_address' => '123 Happy Place',
      'city' => 'New York',
      'skip_geocode' => 1,
      'geo_code_1' => '-42.9771098',
      'geo_code_2' => '147.2687833',
    ]);
    // Return Geometry IDs for which this point from the address is in
    // At present that should be 0 because there is no geometries in the system
    $result = $this->callAPISuccess('Address', 'getgeometries', ['address_id' => $address['id']]);
    $this->assertEquals(0, $result['count']);
    // upperHouseDistrict is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    // We are going to create 2 geometry records 1 being the geometry itself and the other being the MBR of the upperHouseDistrict Geometry
    $upperHouseDistrictJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_tasmanian_upper_house_geometry.json');
    $upperHouseDistrict = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House ',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => $UHCollection['id'],
      'geometry' => trim($upperHouseDistrictJSON),
    ]);
    // Process The Geometry Queue.
    $this->callAPISuccess('Geometry', 'runqueue', []);
    // Now that we have geometries in the system confirm that the cache table has been properly populated
    $result = $this->callAPISuccess('Address', 'getgeometries', ['address_id' => $address['id']]);
    // Ensure that editing an address does not cause a db error.
    $testUpdateAddress = $this->callAPISuccess('Address', 'create', [
      'skip_geocode' => 1,
      'id' => $address['id'],
      'city' => 'Hobart',
    ]);
    $this->assertEquals(1, $result['count']);
    $this->assertEquals($upperHouseDistrict['id'], $result['values'][$result['id']]['geometry_id']);
    // Esure that when we pass skip cache that we still return information back even if the geometry is archived.
    $this->callAPISuccess('Address', 'delete', ['id' => $address['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrict['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $UHGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $UHCollection['id']]);
  }

  /**
   * Test Creating an Entity Relationship between a contact and Geometry.
   */
  public function testCreateRelationshipBetweenContactAndGeometry() {
    // Create a collection
    $UHCollectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $UHCollection = $this->callAPISuccess('GeometryCollection', 'create', $UHCollectionParams);
    // Create a geometry type
    $UHGometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $UHGeometryType = $this->callAPISuccess('GeometryType', 'create', $UHGometryTypeParams);
    $upperHouseDistrictJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_tasmanian_upper_house_geometry.json');
    $upperHouseDistrict = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Sample Tasmanian Upper House ',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => $UHCollection['id'],
      'geometry' => trim($upperHouseDistrictJSON),
    ]);
    $contact = $this->individualCreate();
    $this->callAPISuccess('Geometry', 'createEntity', ['entity_id' => $contact, 'entity_table' => 'civicrm_contact', 'geometry_id' => $upperHouseDistrict['id']]);
    $result = $this->callAPISuccess('Geometry', 'getEntity', ['geometry_id' => $upperHouseDistrict['id']]);
    $this->assertEquals($contact, $result['values'][$result['id']]['entity_id']);
    // Test that we can delete the geometry entity relationship using the id of the table
    $this->callAPISuccess('Geometry', 'deleteentity', ['id' => $result['id']]);
    $result = $this->callAPISuccess('Geometry', 'getEntity', ['geometry_id' => $upperHouseDistrict['id']]);
    // Confirm that we have removed the row from the database
    $this->assertEquals([], $result['values']);
    // Re-create the entity geometry relationship
    $this->callAPISuccess('Geometry', 'createEntity', ['entity_id' => $contact, 'entity_table' => 'civicrm_contact', 'geometry_id' => $upperHouseDistrict['id']]);
    // Check that if we don't supply an id in the entity relationship delete method then we need to supply all 3 params entity_id, entity_table and geometry_id
    $this->callAPIFailure('Geometry', 'deleteentity', ['entity_id' => $contact, 'geometry_id' => $upperHouseDistrict['id']]);
    // Confirm that when we do supply all 3 it succeeds
    $this->callAPISuccess('Geometry', 'deleteentity', ['entity_id' => $contact, 'entity_table' => 'civicrm_contact', 'geometry_id' => $upperHouseDistrict['id']]);
    // Confirm that we have removed the row from the database
    $result = $this->callAPISuccess('Geometry', 'getEntity', ['geometry_id' => $upperHouseDistrict['id']]);
    $this->assertEquals([], $result['values']);
    $this->callAPISuccess('Contact', 'delete', ['id' => $contact, 'skip_undelete']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $upperHouseDistrict['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $UHGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $UHCollection['id']]);
  }

}
