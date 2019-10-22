<?php

require_once 'civigeometry.civix.php';
use CRM_Civigeometry_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civigeometry_civicrm_config(&$config) {
  _civigeometry_civix_civicrm_config($config);
  Civi::service('dispatcher')->addListener('hook_civicrm_post', 'civigeometry_symfony_civicrm_post', -99);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civigeometry_civicrm_xmlMenu(&$files) {
  _civigeometry_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civigeometry_civicrm_install() {
  _civigeometry_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function civigeometry_civicrm_postInstall() {
  _civigeometry_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civigeometry_civicrm_uninstall() {
  _civigeometry_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civigeometry_civicrm_enable() {
  _civigeometry_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civigeometry_civicrm_disable() {
  _civigeometry_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civigeometry_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civigeometry_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civigeometry_civicrm_managed(&$entities) {
  _civigeometry_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civigeometry_civicrm_caseTypes(&$caseTypes) {
  _civigeometry_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function civigeometry_civicrm_angularModules(&$angularModules) {
  _civigeometry_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civigeometry_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civigeometry_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function civigeometry_civicrm_entityTypes(&$entityTypes) {
  _civigeometry_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_permission().
 *
 * Declares CMS based Permissions for this Extension.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission/
 */
function civigeometry_civicrm_permission(&$permissions) {
  $prefix = E::ts('CiviCRM Geometry Extension: ');
  $permissions['administer geometry'] = array(
    $prefix . E::ts('Administer Geometry'),
    E::ts('Create and Update Geometries and Geometry Collections in the System'),
  );
  $permissions['access geometry'] = array(
    $prefix . E::ts('Access Geometry'),
    E::ts('Access Geometries and their collections'),
  );
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * Declares what permissions are required to do what API Access when check_permissions is passed as true.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission/
 */
function civigeometry_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['geometry']['create'] = $permissions['geometry']['delete'] = array(array('administer geometry', 'administer civicrm'));
  $permissions['geometry']['default'] = array('access geometry');
  $permissions['geometry_collection']['create'] = array(array('administer geometry', 'administer civicrm'));
  $permissions['geometry_collection']['default'] = array('access geometry');
  $permissions['geometry_collection']['unarchive'] = $permissions['geometry_collection']['archive'] = $permissions['geometry_collection']['delete'] = $permissions['geometry_collection']['create'];
  $permissions['geometry_type']['create'] = $permissions['geometry_type']['delete'] = array(array('administer geometry', 'administer civicrm'));
  $permissions['geometry_type']['default'] = array('access geometry');
  $permissions['geometry_collection_type'] = $permissions['geometry_type'];
}

/**
 * Implements hook_civicrm_post().
 *
 * This adds records to civigeometry_adddress_geometry whenever an address is updated or created
 * also removes any records from the civigeomety_address_geometry table if the geometry gets archived.
 */
function civigeometry_symfony_civicrm_post($event) {
  $hookValues = $event->getHookValues();
  // Hook value keys are
  // 0 = op
  // 1 = objectName
  // 2 = objectId
  // 3 = objectREf
  if ($hookValues[0] !== 'delete' && $hookValues[0] !== 'geoplace' && $hookValues[1] == 'Address') {
    $queue = CRM_CiviGeometry_Helper::singleton()->getQueue();
    $id = $hookValues[2];
    $address = civicrm_api3('Address', 'get', ['id' => $id])['values'][$id];
    if (!empty($address['geo_code_2']) && !empty($address['geo_code_1'])) {
      $task = new CRM_Queue_Task(
        ['CRM_CiviGeometry_Tasks', 'geoplaceAddress'],
        [$id]
      );
      $queue->createItem($task);
    }
  }
  // If a geometry has been archived ensure that all address records of it in the GeometryEntity table are removed.
  if ($hookValues[0] == 'archive' && $hookValues[1] == 'Geometry') {
    $id = $hookValues[2];
    $dao = new CRM_CiviGeometry_DAO_GeometryEntity();
    $dao->geometry_id = $id;
    $dao->entity_table = 'civicrm_address';
    if ($dao->find()) {
      while ($dao->fetch()) {
        $dao->delete();
      }
    }
  }
  if ($hookValues[0] == 'create' && $hookValues[1] == 'Geometry') {
    $queue = CRM_CiviGeometry_Helper::singleton()->getQueue();
    $task = new CRM_Queue_Task(
        ['CRM_CiviGeometry_Tasks', 'buildGeometryRelationships'],
        [$hookValues[2]]
    );
    $queue->createItem($task);
  }
}
