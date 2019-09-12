<?php
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Address.creategeometries API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_address_creategeometries_spec(&$spec) {
  $spec['address_id'] = [
    'title' => E::ts('Address ID'),
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
    'FKClassName' => 'CRM_Core_BAO_Address',
    'FKApiName' => 'Address',
  ];
  $spec['geometry_id'] = [
    'title' => E::ts('Geometry ID'),
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
    'FKClassName' => 'CRM_CiviGeometry_BAO_Geometry',
    'FKApiName' => 'Geometry',
  ];
}

/**
 * Address.Getgeometries API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_address_creategeometries($params) {
  return _civicrm_api3_basic_create('CRM_CiviGeometry_DAO_AddressGeometry', $params);
}
