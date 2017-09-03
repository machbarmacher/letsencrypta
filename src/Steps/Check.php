<?php

namespace machbarmacher\letsencrypta\Steps;

class Check extends AbstractLetsencryptaStep {
  public function process() {
    return $this->getState()->acmePhpRun('check', [
      'domain' => $this->getState()->getDomain(),
      '--solver' => 'http',
    ]);
  }
}
