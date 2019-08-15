<?php

use CRM_CiviGeometry_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

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
class api_v3_GeometryTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use Civi\Test\Api3DocTrait;
  use Civi\Test\GenericAssertionsTrait;

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
    $queenslandJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'queensland.json');
    $geometryParams = [
      'label' => 'Queensland',
      'geometry_type_id' => $this->stateGeometryType['id'],
      // collection_id accepts an array of ids or a comma separated list of ids.
      'collection_id' => [$this->statesCollection['id']],
      'geometry' => $queenslandJSON,
    ];
    $queensland = $this->callAPISuccess('Geometry', 'create', $geometryParams);
    // RULE: A Geometry can be assigned to one or more collections but never 0
    $geometryCollectionCount = $this->callAPISuccess('Geometry', 'getCollection', ['geometry_id' => $queensland['id']]);
    $this->assertEquals(1, $geometryCollectionCount['count']);
    $this->assertEquals($this->statesCollection['id'], $geometryCollectionCount['values'][$geometryCollectionCount['id']]['collection_id']);
    // Check that the returned geometry matches what was set to be stored. use json_decode function to convert to an array, so that white space is not an issue
    $this->assertEquals(json_decode($queenslandJSON, TRUE), json_decode($queensland['values'][$queensland['id']]['geometry'], TRUE));
    $this->assertEquals(json_decode($queenslandJSON, TRUE), json_decode($this->callAPISuccess('Geometry', 'get', ['id' => $queensland['id']])['values'][$queensland['id']]['geometry'], TRUE));
    // RULE: Geometries can only have 1 type associated.
    $geometryType2 = $this->callAPISuccess('GeometryType', 'Create', ['label' => 'Australian States']);
    $geometryParams['geometry_type_id'] = "{$geometryType2['id']}, {$this->stateGeometryType['id']}";
    $this->callAPIFailure('Geometry', 'create', $geometryParams);
    $geometryParams['geometry_type_id'] = [$geometryType2['id'], $this->stateGeometryType['id']];
    $this->callAPIFailure('Geometry', 'create', $geometryParams);
    // Tear down test data
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType2['id']]);
    // verify that we can delete geometries as well as archiving them. 
    $this->callAPISuccess('Geometry', 'delete', ['id' => $queensland['id']]);
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
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . '12101139836.json');
    $geometryJSONForGzipping = str_replace('"', "'", $geometryJSON);
    $gzipedGeometryJSON = gzencode($geometryJSONForGzipping);
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => '12101139836',
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
  }

  /**
   * Test creating geometry using a specified file as the geometry.
   */
  public function testCreateGeometryFromFile() {
    $collectionParams = [
      'label' => 'NSW Branches',
      'source' => 'Greens NSW',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $NSWBranchesCollection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'Branch',
    ];
    $branchGeometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryFile = \CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'lower_north_shore.json';
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Lower North Shore',
      'geometry_type_id' => $branchGeometryType['id'],
      'collection_id' => [$NSWBranchesCollection['id']],
      'geometry' => $geometryFile,
      'format' => 'file',
    ]);
    $this->assertEquals(json_decode(file_get_contents($geometryFile), TRUE), json_decode($geometry['values'][$geometry['id']]['geometry'], TRUE));
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
    // Create a geometry type
    $UHGometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $UHGeometryType = $this->callAPISuccess('GeometryType', 'create', $UHGometryTypeParams);
    // Nelson is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    // We are going to create 2 geometry records 1 being the geometry itself and the other being the MBR of the Nelson Geometry
    $nelsonJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'nelson.json');
    $nelson = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => [$UHCollection['id']],
      'geometry' => trim($nelsonJSON),
    ]);
    $nelsonMBRData = CRM_Core_DAO::singleValueQuery("SELECT ST_AsGeoJSON(ST_Envelope(geometry)) FROM civigeometry_geometry where id = %1", [1 => [$nelson['id'], 'Positive']]);
    $nelsonMBR = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson MBR',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => [$UHCollection['id']],
      'geometry' => trim($nelsonMBRData),
    ]);
    // Prove that this address is within Nelson
    $individualResult = $this->callAPISuccess('Geometry', 'contains', [
      'geometry_a' => $nelson['id'],
      'geometry_b' => 'POINT(147.2687833 -42.9771098)',
    ]);
    $this->assertEquals(1, $individualResult['values']);
    // Prove that a point that is within the MBR but not the actual geometry returns 0 for an ST_contains on the actual geometry (test RMDS isn't using MBR to do the ST_Contains).
    $nonMBRResult = $this->callAPISuccess('geometry', 'contains', [
     'geometry_a' => $nelson['id'],
     'geometry_b' => 'POINT(147.243 -42.983)',
    ]);
    $this->assertEquals(0, $nonMBRResult['values']);
    // Prove that a point that is within the MBR but not the actual geometry returns 0 for an ST_contains on the MBR geometry
    $mbrResult = $this->callAPISuccess('geometry', 'contains', [
     'geometry_a' => $nelsonMBR['id'],
     'geometry_b' => 'POINT(147.243 -42.983)',
    ]);
    $this->assertEquals(1, $mbrResult['values']);
    // Test that when no geometry is specified that this point is found in both the original poly and the MBR geometry
    $results = $this->callAPISuccess('Geometry', 'contains', [
      'geometry_a' => 0,
      'geometry_b' => 'POINT(147.2687833 -42.9771098)',
    ]);
    $this->assertEquals(2, $results['count']);
    $this->assertContains($nelson['id'], $results['values']);
    $this->assertContains($nelsonMBR['id'], $results['values']);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $nelson['id']]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $nelsonMBR['id']]);
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
    // Nelson is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    $nelsonJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'nelson.json');
    $nelson = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson',
      'geometry_type_id' => $UHGeometryType['id'],
      'collection_id' => [$UHCollection['id']],
      'geometry' => trim($nelsonJSON),
    ]);
    // RULE Geometries must be in at least one collection.
    $this->callAPIFailure('Geometry', 'removecollection', [
      'geometry_id' => $nelson['id'],
      'collection_id' => [$UHCollection['id']],
    ]);
    // tear down created objects
    $this->callAPISuccess('Geometry', 'delete', ['id' => $nelson['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $UHGeometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $UHCollection['id']]);
  }

  /**
   * Remove a Geometry from an collection when the Geometry is in Multiple collections.
   */
  public function testRemoveCollection() {
    $collectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $collection2 = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'Upper House Districts',
      'source' => 'Electoral Commissions',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ]);
    $geometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $nelsonJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'nelson.json');
    // Permit adding multiple collections when creating the geometry
    $nelson = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson',
      'geometry_type_id' => $geometryType['id'],
      // collection_id accepts an array of ids or a comma separated list of ids.
      'collection_id' => "{$collection['id']}, {$collection2['id']}",
      'geometry' => trim($nelsonJSON),
    ]);
    $this->callAPISuccess('Geometry', 'removecollection', [
      'geometry_id' => $nelson['id'],
      'collection_id' => [$collection2['id']],
    ]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $nelson['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection2['id']]);
  }

  /**
   * Test get Geometry Centroid.
   */
  public function testGetGeometryCentroid() {
    $collectionParams = [
      'label' => 'Tas Upper House Districts',
      'source' => 'TASEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $nelsonJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'nelson.json');
    $nelson = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => trim($nelsonJSON),
    ]);
    $centroid = $this->callAPISuccess('Geometry', 'getcentroid', ['id' => $nelson['id']]);
    // Check that the expected points can be found in the array. MariaDB and MySQL each print the array in a different order.
    $this->assertContains('147.29234219', $centroid['values']);
    $this->assertContains('-42.94807285', $centroid['values']);
  }

  /**
   * Test Archiving a Geometry
   */
  public function testArchiveGeometry() {
    $collectionParams = [
      'label' => 'Queensland Wards',
      'source' => 'QEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'LGA Wards',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'cairns_division_9_geo_json.json');
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Cairns Division 9',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => $geometryJSON,
    ]);
    $this->callAPISuccess('Geometry', 'archive', ['id' => $geometry['id']]);
    $geometry = $this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id']]);
    $this->assertEquals(date('Y-m-d h:i:s'), $geometry['values'][$geometry['id']]['archived_date']);
    $this->assertEquals(1, $geometry['values'][$geometry['id']]['is_archived']);
  }

  /**
   * Test Unarchiving a Geometry.
   */
  public function testUnArchiveGeometry() {
    $collectionParams = [
      'label' => 'Queensland Wards',
      'source' => 'QEC',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'LGA Wards',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'cairns_division_9_geo_json.json');
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Cairns Division 9',
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
  }

  /**
   * Test Generating an Overlap Cache.
   */
  public function testOverlapGenerationCache() {
    $queenslandJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'queensland.json');
    $queensland = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Queensland',
      'geometry_type_id' => $this->stateGeometryType['id'],
      'collection_id' => [$this->statesCollection['id']],
      'geometry' => trim($queenslandJSON),
    ]);
    $wardsCollection = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'Queensland Wards',
      'source' => 'QLD',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ]);
    $wardGeometryType = $this->callAPISuccess('GeometryType', 'create', [
      'label' => 'LGA Wards',
    ]);
    $cairnsJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'cairns_division_9_geo_json.json');
    $cairns = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Cairns Division 9',
      'geometry_type_id' => $wardGeometryType['id'],
      'collection_id' => [$wardsCollection['id']],
      'geometry' => trim($cairnsJSON),
    ]);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $cairns['id'],
      'geometry_id_b' => $queensland['id'],
    ]);
    // Check that Cairns Division 9 only covers 4% of Queensland state.
    $this->assertEquals(4, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertFalse($overlap['values'][$overlap['id']]['cache_used']);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $cairns['id'],
      'geometry_id_b' => $queensland['id'],
    ]);
    // Verify calling the API again gets the same result and the cache has been used. 
    $this->assertEquals(4, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertTrue($overlap['values'][$overlap['id']]['cache_used']); 
  }

  /**
   * Test Generating an Overlap between 2 specific geometries is within 97 and 100%
   */
  public function testOverlapGeneration() {
    $sa1JSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . '12101139836.json');
    $sa1 = $this->callAPISuccess('Geometry', 'create', [
      'label' => '1210113836',
      'geometry_type_id' => $this->sa1GeometryType['id'],
      'collection_id' => [$this->sa1Collection['id']],
      'geometry' => trim($sa1JSON),
    ]);
    $wardsCollection = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'NSW Wards',
      'source' => 'NSW',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ]);
    $wardGeometryType = $this->callAPISuccess('GeometryType', 'create', [
      'label' => 'LGA Wards',
    ]);
    $willoughbyNaremburnJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'willoughby_naremburn.json');
    $willoughbyNaremburn = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Cairns Division 9',
      'geometry_type_id' => $wardGeometryType['id'],
      'collection_id' => [$wardsCollection['id']],
      'geometry' => trim($willoughbyNaremburnJSON),
    ]);
    // Get the overlap between the SA1 as inner of the Naremburn wardd
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $sa1['id'],
      'geometry_id_b' => $willoughbyNaremburn['id'],
    ]);
    // MariaDB and MySQL will do these calculations slightly differently but it should be in a 3% range between 100% and 97%.
    $this->assertGreaterThan(97, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertLessThanOrEqual(100, $overlap['values'][$overlap['id']]['overlap']);
    // Assert that the cache is not warmed up at all
    $this->assertFalse($overlap['values'][$overlap['id']]['cache_used']);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $sa1['id'],
      'geometry_id_b' => $willoughbyNaremburn['id'],
    ]);
    // Check that the result came from the cache for performance reasons.
    $this->assertTrue($overlap['values'][$overlap['id']]['cache_used']);
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
    $collectionParams = [
      'label' => 'NSW Branches',
      'source' => 'Greens NSW',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $NSWBranchesCollection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'Branch',
    ];
    $branchGeometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryFile = \CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'lower_north_shore.json';
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Lower North Shore',
      'geometry_type_id' => $branchGeometryType['id'],
      'collection_id' => [$NSWBranchesCollection['id']],
      'geometry' => $geometryFile,
      'format' => 'file',
    ]);
    $spatialData = $this->callAPISuccess('Geometry', 'getspaicaldata', ['id' => $geometry['id']]);
    // Test Accuracy of spatical data.
    //$this->assertEquals('POLYGON((151.126707616 -33.853568996,151.126707616 -33.778527002,151.268936992 -33.778527002,151.268936992 -33.853568996,151.126707616 -33.853568996))',
    //  $spatialData['values'][$geometry['id']]['ST_Envelope']);
    print_r($spatialData['values'][$geometry['id']]['ST_Envelope']);
    $centriodInformation = explode(' ', substr($spatialData['values'][$geometry['id']]['ST_Centroid'],6, -1));
    $this->assertEquals('151.195994', substr($centriodInformation[0], 0, 10));
    $this->assertEquals('-33.8176881', substr($centriodInformation[1], 0, 11));
    $this->assertApproxEquals('57.749', $spatialData['values'][$geometry['id']]['square_km'], 2);
  }

}
