<?php
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Job.Removeexpiredgeometryentity API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_Removeexpiredgeometryentity_spec(&$spec) {
}

/**
 * Job.Removeexpiredgeometryentity API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_job_Removeexpiredgeometryentity($params) {
  $geometryEntites = civicrm_api3('Geometry', 'getentity', [
    'expiry_date' => ['<' => date('Y-m-d H:m:s')],
  ]);
  if (!empty($geometryEntites['values'])) {
    foreach ($geometryEntites['values'] as $geometryEntity) {
      civicrm_api3('Geometry', 'deleteentity', ['id' => $geometryEntity['id']]);
    }
  }
  return civicrm_api3_create_success($geometryEntites['count'], $params, 'Job', 'Removeexpiredgeometryentity');
}
