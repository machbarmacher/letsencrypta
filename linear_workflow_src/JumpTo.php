<?php

namespace machbarmacher\linear_workflow;

class JumpTo extends \Exception {
  /** @var string */
  protected $destination;

  /**
   * Jumpto constructor.
   * @param string $destination
   */
  public function __construct($destination) {
    $this->destination = $destination;
  }

  /**
   * @return string
   */
  public function getDestination() {
    return $this->destination;
  }

}
