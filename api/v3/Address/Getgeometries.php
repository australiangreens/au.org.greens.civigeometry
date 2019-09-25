<?php
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Address.Getgeometries API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_address_getgeometries_spec(&$spec) {
  $spec['address_id'] = [
    'title' => E::ts('Address ID'),
    'type' => CRM_Utils_Type::T_INT,
    'FKClassName' => 'CRM_Core_BAO_Address',
    'FKApiName' => 'Address',
  ];
  $spec['geometry_id'] = [
    'title' => E::ts('Geometry ID'),
    'type' => CRM_Utils_Type::T_INT,
    'FKClassName' => 'CRM_CiviGeometry_BAO_Geometry',
    'FKApiName' => 'Geometry',
  ];
  $spec['skip_cache'] = [
    'title' => E::ts('Skip Cache'),
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'api.default' => 0,
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
function civicrm_api3_address_getgeometries($params) {
  civicrm_api3_verify_one_mandatory($params, NULL, ['address_id', 'geometry_id']);
  if (!empty($params['skip_cache'])) {
    if (!empty($params['address_id'])) {
      $address = civicrm_api3('Address', 'getsingle', ['id' => $params['address_id']]);
      $results = [];
      if (!empty($address['geo_code_1']) && !empty($address['geo_code_2'])) {
        $geometries = civicrm_api3('Geometry', 'contains', [
          'geometry_a' => 0,
          'geometry_b' => 'POINT(' . $address['geo_code_2'] . ' ' . $address['geo_code_1'] . ')',
        ]);
        $key = 0;
        foreach ($geometries['values'] as $geometry) {
          $results[$key] = [
            'address_id' => $params['address_id'],
            'geometry_id' => $geometry,
          ];
          $key++;
        }
        return civicrm_api3_create_success($results, $params);
      }
      return civicrm_api3_create_success(0);
    }
    else {
      return civicrm_api3_create_success(CRM_CiviGeometry_BAO_Geometry::getAddresses($params['geometry_id']), $params);
    }
  }
  $params['entity_table'] = 'civicrm_address';
  if (!empty($params['address_id'])) {
    $params['entity_id'] = $params['address_id'];
  }
  return _civicrm_api3_basic_get('CRM_CiviGeometry_DAO_GeometryEntity', $params);
}
