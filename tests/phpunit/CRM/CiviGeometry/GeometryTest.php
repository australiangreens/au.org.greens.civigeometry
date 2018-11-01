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
class CRM_CiviGeometry_GeometryTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

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
   * Example: Test that a version is returned.
   */
  public function testCreateGeometry() {
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
    $QueenslandJson = file_get_contents(\CRM_Utils_File::addTrailingSlash($this->jsonDirectoryStore) . 'queensland.txt');
    $queensland = $this->callAPISuccess('Geometry', 'create', [
      'label' => 'Queensland',
      'geometry_type_id' => $geometryType['id'],
      'collection_id' => [$collection['id']],
      'geometry' => trim($QueenslandJson),
    ]);
    $this->assertEquals($queensland['values'][$Queensland['id']]['geometry'], $QueenslandJson);
  }

  /**
   * Example: Test that we're using a fake CMS.
   */
  public function testWellFormedUF() {
    $this->assertEquals('UnitTests', CIVICRM_UF);
  }

}
