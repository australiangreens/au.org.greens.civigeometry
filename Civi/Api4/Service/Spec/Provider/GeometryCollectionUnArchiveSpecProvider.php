<?php
namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

class GeometryCollectionUnArchiveSpecProvider implements Generic\SpecProviderInterface {

  /**
   * This runs for both create and get actions
   *
   * @inheritDoc
   */
  public function modifySpec(RequestSpec $spec) {
    $id = new FieldSpec('id', 'GeometryCollection', 'Integer');
    $id->setTitle('Geometry Collection ID')
      ->setDescription('id of the collection to Archive')
      ->setRequired(TRUE);
    $spec->addFieldSpec($id);
  }

  /**
   * @inheritDoc
   */
  public function applies($entity, $action) {
    return $entity === 'GeometryCollection' && in_array($action, ['unarchive']);
  }

}
