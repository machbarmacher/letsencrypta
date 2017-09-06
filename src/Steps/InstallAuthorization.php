<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\Pluggable\AuthorizationInstaller;

class InstallAuthorization extends AbstractLetsencryptaStep {
  public function process() {
    $domains = array_merge([$this->getState()->getDomain()],
      $this->getState()->getAdditionalDomains());
    foreach ($domains as $domain) {
      $this->install($domain);
    }
  }

  private function install($domain) {
    (new AuthorizationInstaller())->install(
      $domain, $this->getState()->getWebroot());
  }

}
