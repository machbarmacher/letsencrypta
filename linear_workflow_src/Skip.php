<?php

namespace machbarmacher\linear_workflow;

class Skip extends \Exception {
  /** @var string[] */
  protected $stepNames;

  /**
   * Skip constructor.
   * @param string[] $stepNames
   */
  public function __construct(array $stepNames) {
    $this->stepNames = $stepNames;
  }

  /**
   * @return string[]
   */
  public function getStepNames() {
    return $this->stepNames;
  }

}
