<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\Pluggable\AuthorizationInstaller;

class RemoveAuthorization extends AbstractLetsencryptaStep {
  public function process() {
    $domains = array_merge([$this->getState()->getDomain()],
      $this->getState()->getAdditionalDomains());
    foreach ($domains as $domain) {
      $this->remove($domain);
    }
  }

  private function remove($domain) {
    (new AuthorizationInstaller())->remove(
      $domain, $this->getState()->getWebroot());
  }

}
