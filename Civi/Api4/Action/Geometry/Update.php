<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Update a Geometry
 * @method bool getformat()
 * @method $this setformat(string $format)
 */
class Update extends \Civi\Api4\Generic\DAOUpdateAction {
  use GeometrySaveTrait;

  /**
   * What format is the Geometry in
   *
   * @var string
   */
  protected $format;

  public function _run(Result $result) {
    $this->formatWriteValues($this->values);
    // Add ID from values to WHERE clause and check for mismatch
    if (!empty($this->values['id'])) {
      $wheres = array_column($this->where, NULL, 0);
      if (!isset($wheres['id'])) {
        $this->addWhere('id', '=', $this->values['id']);
      }
      elseif (!($wheres['id'][1] === '=' && $wheres['id'][2] == $this->values['id'])) {
        throw new \Exception("Cannot update the id of an existing " . $this->getEntityName() . '.');
      }
    }

    // Require WHERE if we didn't get an ID from values
    if (!$this->where) {
      throw new \API_Exception('Parameter "where" is required unless an id is supplied in values.');
    }

    // Update a single record by ID unless select requires more than id
    if ($this->getSelect() === ['id'] && count($this->where) === 1 && $this->where[0][0] === 'id' && $this->where[0][1] === '=' && !empty($this->where[0][2])) {
      $this->values['id'] = $this->where[0][2];
      $objectsToWrite = $this->prepareGeometryParams($this->values);
      $result->exchangeArray($this->writeObjects($objectsToWrite));
      return;
    }

    throw new \API_Exception("Updating multiple Geometries at once is not supported");
  }

  public function validateValues() {
    if (!empty($this->values['collection_id']) && !is_array($this->values['collection_id'])) {
      if (!CRM_Utils_Rule::commaSeparatedIntegers($this->values['collection_id'])) {
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
