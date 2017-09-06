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
    foreach ($domains as $domain) {
      $this->check($domain);
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
