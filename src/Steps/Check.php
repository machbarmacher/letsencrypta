<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;


class Check extends AbstractLetsencryptaStep {
  /**
   * @see \AcmePhp\Cli\Command\CheckCommand
   */
  public function process() {
    AcmePhpApi::run('check', [
      'domain' => $this->getState()->getDomain(),
      '--solver' => 'http',
    ], $this->getState()->getOutput()
    , $this->getState()->isTest());
  }
}
