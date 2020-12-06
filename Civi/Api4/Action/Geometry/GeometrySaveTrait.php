<?php

namespace Civi\Api4\Action\Geometry;

use CRM_CiviGeometry_ExtensionUtil as E;

trait GeometrySaveTrait {

  public function preapreGeometryParams($params) {
    $this->fillDefaults($params);
    $objectsToWrite = [];
    $format = $this->getFormat();
    if (isset($format)) {
      if ($format == 'gzip') {
        try {
          $params['geometry'] = gzdecode($params['geometry']);
        }
        catch (\Exception $e) {
          throw new API_Exception($e->getMessage());
        }
        $params['geometry'] = str_replace("'", '"', $params['geometry']);
      }
      elseif ($format == 'file') {
        if (!file_exists($params['geometry'])) {
          throw new \API_Exception(E::ts('File does not exist'));
        }
        $params['geometry'] = file_get_contents($params['geometry']);
      }
    }
    $json = json_decode($params['geometry'], TRUE);
    if ($json === NULL) {
      throw new \API_Exception(E::ts('Geometry is not proper GeoJSON'));
    }
    // If we have a feature collection we need to process it differently to other forms of GeoJSON.
    if ($json['type'] == 'FeatureCollection') {
      if (empty($params['feature_name_field'])) {
        throw new \API_Exception(E::ts('If loading in a Feature Collection you need to supply the feature_name_field'));
      }
      // Now loop through all the features and add in geometries
      foreach ($json['features'] as $feature) {
        $params['label'] = $feature['properties'][$params['feature_name_field']];
        $params['geometry'] = json_encode($feature['geometry']);
        $objectsToWrite[] = $params;
      }
    }
    else {
      $objectsToWrite[] = $params;
    }
    return $objectsToWrite;
  }

}
