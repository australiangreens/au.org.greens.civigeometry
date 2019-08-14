<?php

use CRM_CiviGeometry_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * FIXME - Add test description.
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

  private $jsonDirectoryStore = __DIR__ . DIRECTORY_SEPARATOR . 'load';

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Example: Test Creating a Geometry.
   */
  public function testCreateGeometry() {
    // Create a collection type
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    // Create a collection
    $collectionParams = [
      'label' => 'States',
      'source' => 'ABS',
      'geometry_collection_type_id' => $collectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create a geometry type
    $geometryTypeParams = [
      'label' => 'States',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    // Load geoJSON file and create a geometry
    $queenslandJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'queensland.json');
    $queensland = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Queensland',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => $queenslandJSON,
    ]);
    // RULE: A Geometry can be assigned to one or more collections but never 0
    $gcg = $this->callAPISuccess('Geometry', 'getCollection', ['geometry_id' => $queensland['id']]);
    $this->assertEquals(1, $gcg['count']);
    $this->assertEquals($collection['id'], $gcg['values'][$gcg['id']]['collection_id']);
    // TODO: geometry matches input geoJSON
    
    // TODO: RULE: Geometry can only be of one Geometry Type
    
    // Tear down test data
    $this->callAPISuccess('Geometry', 'delete', ['id' => $queensland['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection['id']]);
    $this->callAPISuccess('GeometryCollectionType', 'delete', ['id' => $collectionType['id']]);
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
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'SA1',
      'source' => 'ABS',
      'geometry_collection_type_id' => $collectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'States',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . '12101139836.json');
    $geometryJSON = str_replace('"', "'", $geometryJSON);
    $gzipedGeometryJSON = gzencode($geometryJSON);
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => '12101139836',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => $gzipedGeometryJSON,
      'format' => 'gzip',
    ]);
    // RULE: A Geometry can be assigned to one or more collections but never 0
    $gcg = $this->callAPISuccess('Geometry', 'getCollection', ['geometry_id' => $geometry['id']]);
    
    // TODO: Similar tests as per acceptance criteria
  }

  /**
   * Test creating geometry using gzip data
   */
  public function testCreateGeometryFromFile() {
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'NSW Branches',
      'source' => 'Greens NSW',
      'geometry_collection_type_id' => $collectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'States',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $geometryFile = \CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'lower_north_shore.json';
    $geometry = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Queensland',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => $geometryFile,
      'format' => 'file',
    ]);
    $gcg = $this->callAPISuccess('Geometry', 'getCollection', ['geometry_id' => $geometry['id']]);
  }
  
  // TODO: What does this test do?
  public function testMySQLSTContains() {
    // Create a collection type
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    //Create a collection
    $collectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $collectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    // Create a geometry type
    $geometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    // Nelson is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    // We are going to create 2 geometry records 1 being the geometry itself and the other being the MBR of the Nelson Geometry
    $nelsonJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'nelson.json');
    $nelson = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => trim($nelsonJSON),
    ]);
    $nelsonMBRData = CRM_Core_DAO::singleValueQuery("SELECT ST_AsGeoJSON(ST_Envelope(geometry)) FROM civigeometry_geometry where id = %1", [1 => [$nelson['id'], 'Positive']]);
    $nelsonMBR = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson MBR',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => trim($nelsonMBRData),
    ]);
    // Prove that this address is within Nelson
    $individualResult = $this->callAPISuccess('Geometry', 'contains', [
      'geometry_a' => $nelson['id'],
      'geometry_b' => 'POINT(147.2687833 -42.9771098)',
    ]);
    $this->assertEquals(1, $individualResult['values']);
    // Prove that a point that is within the MBR but not the actual geometry returns 0 for an ST_cotains on the actual geometry (test RMDS isn't using MBR to do the ST_Contains).
    $nonMBRResult = $this->callAPISuccess('geometry', 'contains', [
     'geometry_a' => $nelson['id'],
     'geometry_b' => 'POINT(147.243 -42.983)',
    ]);
    $this->assertEquals(0, $nonMBRResult['values']);
    // Prove that a point that is within the MBR but not the actual geometry returns 0 for an ST_cotains on the MBR geometry
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
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection['id']]);
    $this->callAPISuccess('GeometryCollectionType', 'delete', ['id' => $collectionType['id']]);
  }

  /**
   * Test removing collection fails when geometry only belongs in one collection.
   * This is expected to fail as geometry has to be in a collection.
   */
  public function testRemoveOnlyCollection() {
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $collectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    // Nelson is a Tasmanian Upperhouse District as of November 2018
    // It is specifically used as its a smallish area and also has some interesting geometry which makes for showing up
    // Differences between MBR and actual geometry easier.
    $nelsonJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'nelson.json');
    $nelson = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => trim($nelsonJSON),
    ]);
    $this->callAPIFailure('Geometry', 'removecollection', [
      'geometry_id' => $nelson['id'],
      'collection_id' => [$collection['id']],
    ]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $nelson['id']]);
    $this->callAPISuccess('GeometryType', 'delete', ['id' => $geometryType['id']]);
    $this->callAPISuccess('GeometryCollection', 'delete', ['id' => $collection['id']]);
    $this->callAPISuccess('GeometryCollectionType', 'delete', ['id' => $collectionType['id']]);
  }

  /**
   * Remove a Geometry from an collection when the Geometry is in Multiple collections.
   */
  public function testRemoveCollection() {
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'Tasmanian Upper House',
      'source' => 'TasEC',
      'geometry_collection_type_id' => $collectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $collection2 = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'Upper House Districts',
      'source' => 'Electoral Commissions',
      'geometry_collection_type_id' => $collectionType['id'],
    ]);
    $geometryTypeParams = [
      'label' => 'Upper House Districts',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $nelsonJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'nelson.json');
    $nelson = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Nelson',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id'], $collection2['id']],
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
    $this->callAPISuccess('GeometryCollectionType', 'delete', ['id' => $collectionType['id']]);
  }

  /**
   * Test get Geometry Centroid.
   */
  public function testGetGeometryCentroid() {
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'Tas Upper House Districts',
      'source' => 'TASEC',
      'geometry_collection_type_id' => $collectionType['id'],
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
    $this->assertContains('147.29234219', $centroid['values']);
    $this->assertContains('-42.94807285', $centroid['values']);
  }

  /**
   * Test Archiving a Geometry
   */
  public function testArchiveGeometry() {
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'Queensland Wards',
      'source' => 'QEC',
      'geometry_collection_type_id' => $collectionType['id'],
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
    $this->assertEquals(date('Y-m-d h:i:s'), $geometry['values'][$geometry['id']]['archive_date']);
    $this->assertEquals(1, $geometry['values'][$geometry['id']]['is_archived']);
  }

  /**
   * Test Unarchiving a Geometry.
   */
  public function testUnArchiveGeometry() {
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'Queensland Wards',
      'source' => 'QEC',
      'geometry_collection_type_id' => $collectionType['id'],
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
    $this->callAPIFailure('Geometry', 'unarchive', ['id' => $geometry['id']]);
    $this->callAPISuccess('Geometry', 'archive', ['id' => $geometry['id']]);
    $geometry = $this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id']]);
    $this->assertEquals(date('Y-m-d h:i:s'), $geometry['values'][$geometry['id']]['archive_date']);
    $this->assertEquals(1, $geometry['values'][$geometry['id']]['is_archived']);
    $this->callAPISuccess('Geometry', 'unarchive', ['id' => $geometry['id']]);
    $geometry = $this->callAPISuccess('Geometry', 'get', ['id' => $geometry['id']]);
    $this->assertEquals(0, $geometry['values'][$geometry['id']]['is_archived']);
  }

  /**
   * Test Generating an Overlap Cache.
   */
  public function testOverlapGeneration() {
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'States',
      'source' => 'ABS',
      'geometry_collection_type_id' => $collectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'States',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $queenslandJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'queensland.json');
    $queensland = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Queensland',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => trim($queenslandJSON),
    ]);
    $collection2 = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'Queensland Wards',
      'source' => 'QLD',
      'geometry_collection_type_id' => $collectionType['id'],
    ]);
    $geometryType2 = $this->callAPISuccess('GeometryType', 'create', [
      'label' => 'LGA Wards',
    ]);
    $cairnsJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'cairns_division_9_geo_json.json');
    $cairns = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Cairns Division 9',
      'geometry_type_id' => $geometryType2['id'],
      'collection_id' => [$collection2['id']],
      'geometry' => trim($cairnsJSON),
    ]);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $cairns['id'],
      'geometry_id_b' => $queensland['id'],
    ]);
    $this->assertEquals(4, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertFalse($overlap['values'][$overlap['id']]['cache_used']);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $cairns['id'],
      'geometry_id_b' => $queensland['id'],
    ]);
    $this->assertEquals(4, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertTrue($overlap['values'][$overlap['id']]['cache_used']); 
  }

  /**
   * Test Generating an Overlap with known >95% overlap.
   */
  public function testOverlapGeneration90() {
    $collectionTypeParams = [
      'label' => 'External',
    ];
    $collectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $collectionTypeParams);
    $collectionParams = [
      'label' => 'SA1s',
      'source' => 'ABS',
      'geometry_collection_type_id' => $collectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $collectionParams);
    $geometryTypeParams = [
      'label' => 'SA1s',
    ];
    $geometryType = $this->callAPISuccess('GeometryType', 'create', $geometryTypeParams);
    $sa1JSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . '12101139836.json');
    $sa1 = $this->callAPISuccess('Geometry', 'create', [
      'label' => '1210113836',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => trim($sa1JSON),
    ]);
    $collection2 = $this->callAPISuccess('GeometryCollection', 'create', [
      'label' => 'NSW Wards',
      'source' => 'NSW',
      'geometry_collection_type_id' => $collectionType['id'],
    ]);
    $geometryType2 = $this->callAPISuccess('GeometryType', 'create', [
      'label' => 'LGA Wards',
    ]);
    $willoughbyNaremburnJSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'willoughby_naremburn.json');
    $willoughbyNaremburn = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Cairns Division 9',
      'geometry_type_id' => $geometryType2['id'],
      'collection_id' => [$collection2['id']],
      'geometry' => trim($willoughbyNaremburnJSON),
    ]);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $sa1['id'],
      'geometry_id_b' => $willoughbyNaremburn['id'],
    ]);
    $this->assertGreaterThan(97, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertLessThanOrEqual(100, $overlap['values'][$overlap['id']]['overlap']);
    $this->assertFalse($overlap['values'][$overlap['id']]['cache_used']);
    $overlap = $this->callAPISuccess('Geometry', 'getoverlap', [
      'geometry_id_a' => $sa1['id'],
      'geometry_id_b' => $willoughbyNaremburn['id'],
    ]);
    $this->assertTrue($overlap['values'][$overlap['id']]['cache_used']);
  }

  /**
   * Test getting a distance
   * @note Postgres reported 2,202 meeters here however MySQL5.7 using native functions returned 2,197
   */
  public function testGetDistance() {
    $result = $this->callAPISuccess('Geometry', 'getdistance', [
     'geometry_a' => 'POINT(147.2687833 -42.9771098)',
     'geometry_b' => 'POINT(147.243 -42.983)',
    ]);
    $this->assertEquals('2197', (int) $result['values']);
  }

}