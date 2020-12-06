<?php
namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Get GeometryEntity Records
 */
class GetEntity extends \Civi\Api4\Generic\AbstractGetAction {

  public function _run(Result $result) {
    $whereClauses = $this->getWhere();
    $query = 'SELECT * FROM ' . \CRM_CiviGeometry_DAO_GeometryEntity::getTableName();
    $where = '';
    foreach ($whereClauses as $whereClause) {
      if (in_array($whereClause[0], ['entity_id', 'entity_Table', 'geometry_id', 'expiry_date'])) {
        $value = $whereClause[2];
        if ($whereClause[0] === 'expiry_date') {
          $value = \CRM_Utils_Date::isoToMysql($value);
        }
        if (!empty($where)) {
          $where .= ' AND ';
        }
        $where .= $whereClause[0] . $whereClause[1] . $value;
      }
    }
    if (!empty($where)) {
      $query .= ' WHERE ' . $where;
    }
    $results = \CRM_Core_DAO::executeQuery($query);
    while ($results->fetch()) {
      $result[] = [
        'id' => $results->id,
        'entity_id' => $results->entity_id,
        'entity_table' => $results->entity_table,
        'geometry_id' => $results->geometry_id,
      ];
    }
  }

}
