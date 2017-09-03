<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\State;
use machbarmacher\linear_workflow\AbstractStep;

abstract class AbstractLetsencryptaStep extends AbstractStep {
  /** @var \machbarmacher\letsencrypta\State */
  private $state;

  /**
   * AbstractLetsencryptaStep constructor.
   * @param \machbarmacher\letsencrypta\State $state
   */
  public function __construct(State $state) {
    $this->state = $state;
  }

  /**
   * @return \machbarmacher\letsencrypta\State
   */
  public function getState() {
    return $this->state;
  }

}
