<?php
namespace Civi\Api4\Action\Geometry;

use Civi\Api4\Generic\Result;
use CRM_CiviGeometry_ExtensionUtil as E;

/**
 * Get GeometryEntity Records
 */
class GetEntity extends \Civi\Api4\Generic\DAOGetAction {

  public function _run(Result $result) {
    $whereClauses = $this->getWhere();
    $tableName = \CRM_CiviGeometry_DAO_GeometryEntity::getTableName();
    $query = "SELECT * FROM {$tableName}";
  
    $conditions = [];
    $allowedFields = ['entity_id', 'entity_table', 'geometry_id', 'expiry_date'];

    foreach ($whereClauses as $whereClause) {
      [$field, $operator, $rawValue] = $whereClause;
  
      if (in_array($field, $allowedFields)) {
        $value = ($field === 'expiry_date') 
          ? \CRM_Utils_Date::isoToMysql($rawValue) 
          : $rawValue;
  
        $conditions[] = \CRM_Contact_BAO_Query::buildClause($field, $operator, $value);
      }
    }

    if (!empty($conditions)) {
      $query .= ' WHERE ' . implode(' AND ', $conditions);
    }
  
    $results = \CRM_Core_DAO::executeQuery($query);
  
    while ($results->fetch()) {
      $result[] = [
        'id'           => $results->id,
        'entity_id'    => $results->entity_id,
        'entity_table' => $results->entity_table,
        'geometry_id'  => $results->geometry_id,
      ];
    }
  }

}
