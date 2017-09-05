<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\letsencrypta\State;
use machbarmacher\linear_workflow\Skip;

class Plan extends AbstractLetsencryptaStep {
  /** @var bool */
  private $forceRegister;

  public function __construct(State $state, $forceRegister) {
    parent::__construct($state);
    $this->forceRegister = $forceRegister;
  }

  public function process() {
    if ($this->forceRegister) {
      return;
    }
    // Skip registration if we have an acount key pair.
    // This might break if keys are generated but not registered.
    // @todo Patch AcmePhp to create a registration artefact.
    $repository = AcmePhpApi::getRepository();
    $done = $repository->hasAccountKeyPair();
    if ($done) {
      throw new Skip(['Register']);
    }
  }

}
