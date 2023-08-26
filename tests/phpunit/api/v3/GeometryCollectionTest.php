<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * This test provides coverage for the API methods relating to the GeometryCollection entity
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
class api_v3_GeometryCollectionTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use Civi\Test\Api3DocTrait;

  private $internalCollectionType;
  private $externalCollectionType;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp(): void {
    parent::setUp();
    $internalCollectionTypeParams = [
      'label' => 'Internal',
    ];
    $this->internalCollectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $internalCollectionTypeParams);
    $externalCollectionTypeParams = [
      'label' => 'External',
    ];
    $this->externalCollectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $externalCollectionTypeParams);
  }

  public function tearDown(): void {
    parent::tearDown();
  }

  /**
   * Test Create GeometryCollection.
   * @dataProvider versionThreeAndFour
   */
  public function testCreateGeometryCollection($apiVersion): void {
    $this->_apiversion = $apiVersion;
    $params = [
      'label' => 'NSW State LH',
      'description' => 'NSW State Lower House Elecroates',
      'origin' => 'NSW Electoral Commission',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $this->callAPISuccess('GeometryCollection', 'create', $params);
  }

  /**
   * Test that we can create multiple geometry collections of the same type
   * @dataProvider versionThreeAndFour
   */
  public function testMultipleGeometryCollectionsSameType($apiVersion): void {
    $this->_apiversion = $apiVersion;
    $params1 = [
      'label' => 'NSW State LH',
      'description' => 'NSW State Lower House Elecroates',
      'origin' => 'NSW Electoral Commission',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $params2 = [
      'label' => 'VIC State LH',
      'description' => 'VIC State Lower House Elecroates',
      'origin' => 'VIC Electoral Commission',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $this->callAPISuccess('GeometryCollection', 'create', $params1);
    $this->callAPISuccess('GeometryCollection', 'create', $params2);
  }

  /**
   * Test that no duplicate Collections can be created
   * A duplicate is understood in terms of identical types and labels
   * @dataProvider versionThreeAndFour
   */
  public function testNoDuplicateGeometryCollections($apiVersion): void {
    $this->_apiversion = $apiVersion;
    $params = [
      'label' => 'NSW State LH',
      'description' => 'NSW State Lower House Elecroates',
      'origin' => 'NSW Electoral Commission',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $this->callAPISuccess('GeometryCollection', 'create', $params);
    $this->callAPIFailure('GeometryCollection', 'create', $params);
  }

  /**
   * Test Archiving a Geometry collection
   * @dataProvider versionThreeAndFour
   */
  public function testArchivingCollection($apiVersion): void {
    $this->_apiversion = $apiVersion;
    $params = [
      'label' => 'NSW State LH',
      'description' => 'NSW State Lower House Elecroates',
      'origin' => 'NSW Electoral Commission',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $params);
    $collection = $this->callAPISuccess('GeometryCollection', 'archive', ['id' => $collection['id']]);
    $key = $apiVersion === 3 ? $collection['id'] : 0;
    $this->assertEquals(1, $collection['values'][$key]['is_archived']);
    $this->assertEquals(date('Ymdhis'), $collection['values'][$key]['archived_date']);
  }

  /**
   * Test Unarchiving a geometry collection
   * @dataProvider versionThreeAndFour
   */
  public function testUnArchivingCollection($apiVersion): void {
    $this->_apiversion = $apiVersion;
    $params = [
      'label' => 'NSW State LH',
      'description' => 'NSW State Lower House Elecroates',
      'origin' => 'NSW Electoral Commission',
      'geometry_collection_type_id' => $this->externalCollectionType['id'],
    ];
    $collection = $this->callAPISuccess('GeometryCollection', 'create', $params);
    // Test that calling unarchive on a non archived collection fails.
    $this->callAPIFailure('GeometryCollection', 'unarchive', ['id' => $collection['id']]);
    $this->callAPISuccess('GeometryCollection', 'archive', ['id' => $collection['id']]);
    $collection = $this->callAPISuccess('GeometryCollection', 'unarchive', ['id' => $collection['id']]);
    $key = $apiVersion === 3 ? $collection['id'] : 0;
    $this->assertEquals(0, $collection['values'][$key]['is_archived']);
  }

}
