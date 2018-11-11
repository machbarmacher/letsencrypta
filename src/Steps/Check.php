<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;


class Check extends AbstractLetsencryptaStep {
  /**
   * @see \AcmePhp\Cli\Command\CheckCommand
   */
  public function process() {
    $domains = array_merge([$this->getState()->getDomain()],
      $this->getState()->getAdditionalDomains());
    $this->check($domains);
  }

  /**
   * @param $domains
   */
  protected function check($domains) {
    AcmePhpApi::run('check', [
      'domains' => $domains,
      '--solver' => 'http',
      // Guzzle hickups on first test without cert otherwise.
      '--no-test' => TRUE,
    ], $this->getState()->getOutput()
    , $this->getState()->isTest());
  }
}
