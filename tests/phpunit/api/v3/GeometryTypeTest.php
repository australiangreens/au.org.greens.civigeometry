<?php

use CRM_CiviGeometry_ExtensionUtil as E;
use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * This class tests creating a geometry type.
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
class api_v3_GeometryTypeTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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
   * Tests that we can createa a Geometry Type
   */
  public function testCreateGeometryType() {
    $params = [
      'label' => 'State Lower House Districts',
      'description' => 'Geometry Representing the State Lower House Districts around Australia',
    ];
    $this->callAPIAndDocument('GeometryType', 'create', $params, __FUNCTION__, __FILE__);
  }

  /**
   * Test that we cannot create duplicate types.
   * Uniqueness is defined by label being unique.
   */
  public function testNoDuplicateTypes() {
    $params = [
      'label' => 'State Lower House Districts',
      'description' => 'Geometry Representing the State Lower House Districts around Australia',
    ];
    // The tesst should pass and create a geometry type but fail eh 2nd time as the label is already there. 
    $this->callAPISuccess('GeometryType', 'create', $params);
    $this->callAPIFailure('GeometryType', 'create', $params);
  }

}
