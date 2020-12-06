<?php
namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Get Geometry Collections
 */
class GetCollection extends \Civi\Api4\Generic\AbstractGetAction {

  public function _run(Result $result) {
    $whereClauses = $this->getWhere();
    $query = 'SELECT * FROM ' . \CRM_CiviGeometry_DAO_GeometryCollectionGeometry::getTableName();
    $where = '';
    foreach ($whereClauses as $whereClause) {
      if (in_array($whereClause[0], ['id', 'collection_id', 'geometry_id'])) {
        $field = ($whereClause[0] === 'id' ? 'geometry_id' : $whereClause[0]);
        if (!empty($where)) {
          $where .= ' AND ';
        }
        $where .= $field . ' IN (' . (is_array($whereClause[2]) ? implode(',', $whereClause[2]) : $whereClause[2]) . ')';
      }
    }
    if (!empty($where)) {
      $query .= ' WHERE ' . $where;
    }
    $results = \CRM_Core_DAO::executeQuery($query);
    while ($results->fetch()) {
      $result[] = [
        'id' => $results->id,
        'geometry_id' => $results->geometry_id,
        'collection_id' => $results->collection_id,
      ];
    }
  }

}
