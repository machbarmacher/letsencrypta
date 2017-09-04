<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

/**
 * Class Authorize
 * @package machbarmacher\letsencrypta\Steps
 * @see
 */
class Authorize extends AbstractLetsencryptaStep {
  public function process() {
    return AcmePhpApi::run('authorize', [
      'domain' => $this->getState()->getDomain(),
      '--solver' => 'http',
    ], $this->getState()->getOutput()
    , $this->getState()->isStaging());
  }
}
