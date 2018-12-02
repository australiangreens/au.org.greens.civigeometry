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
  $prefix = E::ts('CiviCRM Geometry Extension');
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
  $permissions['geometry_collection_type'] = $$permissions['geometry_type'];
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function civigeometry_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function civigeometry_civicrm_navigationMenu(&$menu) {
  _civigeometry_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _civigeometry_civix_navigationMenu($menu);
} // */
