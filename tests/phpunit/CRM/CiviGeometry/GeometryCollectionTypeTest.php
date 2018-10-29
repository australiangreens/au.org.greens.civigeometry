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
class CRM_CiviGeometry_GeometryCollectionTypeTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use Civi\Test\Api3DocTrait;

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
   * Test Creating a Geometry Collection Type.
   */
  public function testCreateGeometryCollectionType() {
    $params = [
      'label' => 'NSW Lower',
      'description' => 'NSW Lower House electorates',
    ];
    $this->callAPIAndDocument('GeometryCollectionType', 'create', $params, __FUNCTION__, __FILE__);
  }

  /**
   * Test Create Geometry Collection Type without description.
   */
  public function testGeometryCollectionTypeNoDescription() {
    $params = [
      'label' => 'NSW Branches',
    ];
    $this->callAPISuccess('GeometryCollectionType', 'create', $params);
  }

}
