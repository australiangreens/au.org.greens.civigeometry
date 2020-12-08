<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Create a Geometry
 * @method bool getformat()
 * @method $this setformat(string $format)
 */
class Create extends \Civi\Api4\Generic\DAOCreateAction {
  use GeometrySaveTrait;

  /**
   * What format is the Geometry in
   *
   * @var string
   */
  protected $format;

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    $this->formatWriteValues($this->values);
    $this->validateValues();
    $params = $this->values;
    $this->fillDefaults($params);
    $objectsToWrite = $this->prepareGeometryParams($params);
    $resultArray = $this->writeObjects($objectsToWrite);
    $result->exchangeArray($resultArray);
  }

  public function validateValues() {
    if (!empty($this->values['collection_id']) && !is_array($this->values['collection_id'])) {
      if (!\CRM_Utils_Rule::commaSeparatedIntegers($this->values['collection_id'])) {
        throw new \API_Exception(E::ts('collection_id is not a valid list of ids'));
      }
      $this->values['collection_id'] = explode(',', $this->values['collection_id']);
    }
    if (isset($this->values['geomety']) && empty($this->values['geometry'])) {
      throw new \API_Exception(E::ts('Geometry was empty'));
    }
    parent::validateValues();
  }

}
