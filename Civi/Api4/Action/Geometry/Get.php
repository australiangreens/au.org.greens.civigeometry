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
   * @var int
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
      $collectionResults = \Civi\Api4\Geometry::getCollection(FALSE)->addWhere('collection_id', '=', $this->collectionId);
      $this->addWhere('id', 'IN', \CRM_Utils_Array::collect('geometry_id', $collectionResults));
    }
    $this->setDefaultWhereClause();
    $this->expandSelectClauseWildcards();
    $this->getObjects($result);
    foreach ($result as $key => $res) {
      $kml = FALSE;
      $mySQLFunction = 'ST_AsGeoJSON';
      if ($this->format && in_array($this->format, ['kml', 'wkt'])) {
        $mySQLFunction = 'ST_AsText';
        if ($this->format === 'kml') {
          $kml = TRUE;
        }
      }
      $select = $this->getSelect();
      if (empty($select) || in_array('geometry', $select) || in_array('*', $select)) {
        $geometry = \CRM_Core_DAO::singleValueQuery("SELECT {$mySQLFunction}(geometry) FROM civigeometry_geometry WHERE id = %1", [1 => [$res['id'], 'Positive']]);
        if ($kml) {
          $geometry = \CRM_CiviGeometry_BAO_Geometry::wkt2kml($geometry);
        }
        $result[$key]['geometry'] = $geometry;
      }
    }
  }

}
