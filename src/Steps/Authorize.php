<?php

namespace machbarmacher\letsencrypta\Steps;

class Authorize extends AbstractLetsencryptaStep {
  public function process() {
    return $this->getState()->acmePhpRun('register', [
      'domain' => $this->getState()->getDomain(),
      '--solver' => 'http',
    ]);
  }
}
