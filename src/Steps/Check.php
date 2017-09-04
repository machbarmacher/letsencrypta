<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

class Check extends AbstractLetsencryptaStep {
  public function process() {
    return AcmePhpApi::run('check', [
      'domain' => $this->getState()->getDomain(),
      '--solver' => 'http',
    ], $this->getState()->getOutput());
  }
}