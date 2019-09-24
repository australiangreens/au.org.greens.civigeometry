<?php

/**
 * This is a helper class for the queue functionality.
 * It is a singleton class because it will hold the queue object for our extension
 */
class CRM_CiviGeometry_Helper {
  const QUEUE_NAME = 'au.org.greens.civigeometry.queue';

  /**
   * @var CRM_CiviGeometry_Helper();
   */
  private $queue;

  public static $singleton;

  /**
   * @return CRM_CiviGeometry_Helper
   */
  public static function singleton() {
    if (!self::$singleton) {
      self::$singleton = new CRM_CiviGeometry_Helper();
    }
    return self::$singleton;
  }

  private function __construct() {
    $this->queue = CRM_Queue_Service::singleton()->create(array(
      'type' => 'Sql',
      'name' => self::QUEUE_NAME,
      // do not flush queue upon creation
      'reset' => FALSE,
    ));
  }

  /**
   * @return CRM_Queue_Queue
   */
  public function getQueue() {
    return $this->queue;
  }

}
