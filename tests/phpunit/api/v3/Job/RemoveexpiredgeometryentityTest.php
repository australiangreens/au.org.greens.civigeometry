<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;

/**
 * Job.Removeexpiredgeometryentity API Test Case
 * This is a generic test class implemented with PHPUnit.
 * @group headless
 */
class api_v3_Job_RemoveexpiredgeometryentityTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface {

  use \Civi\Test\Api3DocTrait;
  use \Civi\Test\GenericAssertionsTrait;
  use \Civi\Test\ContactTestTrait;

  private $jsonDirectoryStore = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'load';

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
   * Simple example test case.
   *
   * Note how the function name begins with the word "test".
   */
  public function testExpiredJob() {
    // Load geoJSON file and create a geometry
    $sa1JSON = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'sample_sa1_geometry.json');
    // Create SA1 Geometry
    $sa1 = $this->callAPISuccess('Geometry', 'create', [
      'label' => '1210113836',
      'geometry_type_id' => $this->sa1GeometryType['id'],
      'collection_id' => [$this->sa1Collection['id']],
      'geometry' => trim($sa1JSON),
    ]);
    $contact1 = $this->individualCreate();
    $contact2 = $this->individualCreate();
    $this->callAPISuccess('Geometry', 'createentity', [
      'entity_id' => $contact1,
      'entity_table' => 'civicrm_contact',
      'geometry_id' => $sa1['id'],
    ]);
    $this->callAPISuccess('Geometry', 'createentity', [
      'entity_id' => $contact2,
      'entity_table' => 'civicrm_contact',
      'geometry_id' => $sa1['id'],
      'expiry_date' => date('Y-m-d H:m:s', strtotime('-1 month')),
    ]);
    $currentGeometryEntities = $this->callAPISuccess('Geometry', 'getentity', ['geometry_id' => $sa1['id']]);
    $this->assertEquals(2, $currentGeometryEntities['count']);
    $result = civicrm_api3('Job', 'removeexpiredgeometryentity');
    $currentGeometryEntities = $this->callAPISuccess('Geometry', 'getentity', ['geometry_id' => $sa1['id']]);
    $this->assertEquals(1, $currentGeometryEntities['count']);
    $this->assertEquals($contact1, $currentGeometryEntities['values'][$currentGeometryEntities['id']]['entity_id']);
    $this->callAPISuccess('Geometry', 'deleteentity', ['id' => $currentGeometryEntities['id']]);
    $this->callAPISuccess('Contact', 'delete', ['id' => $contact2, 'skip_undelete' => 1]);
    $this->callAPISuccess('Contact', 'delete', ['id' => $contact1, 'skip_undelete' => 1]);
    $this->callAPISuccess('Geometry', 'delete', ['id' => $sa1['id']]);
  }

}
