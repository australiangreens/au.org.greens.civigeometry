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
class CRM_CiviGeometry_GeometryCollectionTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  use Civi\Test\Api3DocTrait;

  private $nswElecorateCollectionType;
  private $nswBranchesCollectionType;

  public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    parent::setUp();
    $nswElectorateCollectionTypeParams = [
      'label' => 'NSW Electorates',
      'description' => 'NSW Lower House Elecroates',
    ];
    $this->nswElecorateCollectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $nswElectorateCollectionTypeParams);
    $nswBranchesCollectionTypeParams = [
      'label' => 'NSW Branches',
    ];
    $this->nswElecorateCollectionType = $this->callAPISuccess('GeometryCollectionType', 'create', $nswBranchesCollectionTypeParams);
  }

  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Test Create GeometryCollection.
   */
  public function testCreateGeometryCollection() {
    $params = [
      'label' => 'NSW State LH',
      'description' => 'NSW State Lower House Elecroates',
      'origin' => 'NSW Electoral Commission',
      'geometry_collection_type' => $this->nswElecorateCollectionType['id'],
    ];
    $this->callAPIAndDocument('GeometryCollection', 'create', $params, __FUNCTION__, __FILE__);
  }

}
