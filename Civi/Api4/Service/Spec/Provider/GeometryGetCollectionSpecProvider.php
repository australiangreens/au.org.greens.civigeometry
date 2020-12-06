<?php
namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

class GeometryGetCollectionSpecProvider implements Generic\SpecProviderInterface {

  /**
   * This runs for both create and get actions
   *
   * @inheritDoc
   */
  public function modifySpec(RequestSpec $spec) {
    $collection_id = new FieldSpec('collection_id', 'GeometryCollection', 'Integer');
    $collection_id->setTitle('Geometry Collection ID')
      ->setDescription('Geometry Collection to limit to')
      ->setRequired(FALSE)
      ->setFKentity('GeometryCollection');
    $spec->addFieldSpec($collection_id);
    $geometry_id = new FieldSpec('geometry_id', 'Geometry', 'Integer');
    $geometry_id->setTitle('Geometry ID')
      ->setDescription('Geometry to limit to')
      ->setRequired(FALSE)
      ->setFKentity('Geometry');
    $spec->addFieldSpec($geometry_id);
  }

  /**
   * @inheritDoc
   */
  public function applies($entity, $action) {
    return $entity === 'Geometry' && in_array($action, ['getCollection']);
  }

}
