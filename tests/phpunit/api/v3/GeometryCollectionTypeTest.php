<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * This test provides coverage for the API method that lets you create geometry collection types
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
class api_v3_GeometryCollectionTypeTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use Civi\Test\Api3DocTrait;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp(): void {
    parent::setUp();
  }

  public function tearDown(): void {
    parent::tearDown();
  }

  /**
   * Test creating a Geometry Collection Type.
   * @dataProvider versionThreeAndFour
   */
  public function testCreateGeometryCollectionType($apiVersion): void {
    $this->_apiversion = $apiVersion;
    $params = [
      'label' => 'External',
      'description' => 'Externally created Geometry Collection',
    ];
    $this->callAPISuccess('GeometryCollectionType', 'create', $params);
  }

  /**
   * Test create Geometry Collection Type without description.
   * @dataProvider versionThreeAndFour
   */
  public function testGeometryCollectionTypeNoDescription($apiVersion): void {
    $this->_apiversion = $apiVersion;
    $params = [
      'label' => 'External',
    ];
    $this->callAPISuccess('GeometryCollectionType', 'create', $params);
  }

  /**
   * Test that you cannot create duplicate Collection Types.
   * @dataProvider versionThreeAndFour
   */
  public function testNoDuplicateGeometryCollectionTypes($apiVersion): void {
    $this->_apiversion = $apiVersion;
    $params = [
      'label' => 'Internal',
    ];
    $this->callAPISuccess('GeometryCollectionType', 'create', $params);
    $this->callAPIFailure('GeometryCollectionType', 'create', $params);
  }

}
