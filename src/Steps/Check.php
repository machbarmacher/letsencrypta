<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;


class Check extends AbstractLetsencryptaStep {
  /**
   * @see \AcmePhp\Cli\Command\CheckCommand
   */
  public function process() {
    $this->check($this->getState()->getDomain());
    foreach ($this->getState()->getAdditionalDomains() as $additionalDomain) {
      $this->check($additionalDomain);
    }
  }

  /**
   * @param $domain
   */
  protected function check($domain) {
    AcmePhpApi::run('check', [
      'domain' => $domain,
      '--solver' => 'http',
    ], $this->getState()->getOutput()
    , $this->getState()->isTest());
  }
}
