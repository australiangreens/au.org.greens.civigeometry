<?php

namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Get a Geometry
 * @method bool getformat()
 * @method $this setformat(string $format)
 * @method bool getCollectionId()
 * @method $this setCollectionId(string $format)
 */
class Get extends \Civi\Api4\Generic\DAOGetAction {

  /**
   * Format to return Geometry in.
   * @var string
   */
  protected $format;

  /**
   * Collection ID to limit geometries to
   * @var int|array
   */
  protected $collectionId;

  public function _run(Result $result) {
    // Early return if table doesn't exist yet due to pending upgrade
    if (!empty($this->format) && !in_array($this->format, ['json', 'kml', 'wkt'])) {
      throw new \API_Exception(E::ts('Output format must be one of json, kml or wkt'));
    }
    $baoName = $this->getBaoName();
    if (!$baoName::tableHasBeenAdded()) {
      \Civi::log()->warning("Could not read from {$this->getEntityName()} before table has been added. Upgrade required.", ['civi.tag' => 'upgrade_needed']);
      return;
    }
    $collection = !empty($this->collectionId);
    if ($collection) {
      if (is_array($this->collectionId)) {
        $operator = key($this->collectionId);
        $value = $this->collectionId[$operator];
      }
      else {
        $operator = '=';
        $value = $this->collectionId;
      }
      $collectionResults = \Civi\Api4\Geometry::getCollection(FALSE)->addWhere('collection_id', $operator, $value)->execute()->getArrayCopy();
      $this->addWhere('id', 'IN', \CRM_Utils_Array::collect('geometry_id', $collectionResults));
    }
    $this->setDefaultWhereClause();
    $this->expandSelectClauseWildcards();
    $this->getObjects($result);

    $select = $this->getSelect();
    // Only proceed if geometry field is requested
    if (empty($select) || in_array('geometry', $select) || in_array('*', $select)) {
      $geomIDs = array_keys($result->getArrayCopy());

      if (empty($geomIDs)) {
        return;
      }

      $mySQLFunction = 'ST_AsGeoJSON';
      $isKml = false;
      if ($this->format && in_array($this->format, ['kml', 'wkt'])) {
        $mySQLFunction = 'ST_AsText';
        if ($this->format === 'kml') {
          $isKml = TRUE;
        }
      }

      $geometries = [];
      $batchSize = 500;

      foreach (array_chunk($geomIDs, $batchSize) as $idBatch) {
        $dao = \CRM_Core_DAO::executeQuery(
          "SELECT id, {$mySQLFunction}(geometry) as geom FROM civigeometry_geometry WHERE id IN (%1)",
          [1 => [implode(',', $idBatch), 'CommaSeparatedIntegers']]
        );
        while ($dao->fetch()) {
          $geometries[$dao->id] = $isKml ? \CRM_CiviGeometry_BAO_Geometry::wkt2kml($dao->geom) : $dao->geom;
        }
      }

      foreach ($result as $key => $res) {
        if (isset($geometries[$res['id']])) {
          $result[$key]['geometry'] = $geometries[$res['id']];
        }
      }
    }
  }

}
