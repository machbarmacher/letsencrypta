<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

class Authorize extends AbstractLetsencryptaStep {
  public function process() {
    return AcmePhpApi::acmePhpRun('register', [
      'domain' => $this->getState()->getDomain(),
      '--solver' => 'http',
    ], $this->getState()->getOutput());
  }
}
