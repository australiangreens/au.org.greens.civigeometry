<?php
namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

class GeometryGetEntitySpecProvider implements Generic\SpecProviderInterface {

  /**
   * This runs for both create and get actions
   *
   * @inheritDoc
   */
  public function modifySpec(RequestSpec $spec) {
    $entity_id = new FieldSpec('entity_id', 'GeometryEntity', 'Integer');
    $entity_id->setTitle('Entity ID')
      ->setDescription('Entity ID that is linked to the geometry')
      ->setRequired(FALSE);
    $spec->addFieldSpec($entity_id);
    $entity_table = new FieldSpec('entity_table', 'GeometryEntity', 'String');
    $entity_table->setTitle('Entity Table')
      ->setDescription('Entity Table for the entity that is linked to the geometry')
      ->setRequired(FALSE);
    $spec->addFieldSpec($entity_table);
    $geometry_id = new FieldSpec('geometry_id', 'Geometry', 'Integer');
    $geometry_id->setTitle('Geometry ID')
      ->setDescription('Geometry to limit to')
      ->setRequired(FALSE)
      ->setFKentity('Geometry');
    $spec->addFieldSpec($geometry_id);
    $expiry_date = new FieldSpec('expiry_date', 'GeometryEntity', 'Timestamp');
    $expiry_date->setTitle('Entity Table')
      ->setDescription('When does the Geometry linkage expire')
      ->setRequired(FALSE);
    $spec->addFieldSpec($expiry_date);
  }

  /**
   * @inheritDoc
   */
  public function applies($entity, $action) {
    return $entity === 'Geometry' && in_array($action, ['getEntity']);
  }

}
