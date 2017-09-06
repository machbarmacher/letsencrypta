<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\Pluggable\AuthorizationInstaller;

class InstallAuthorization extends AbstractLetsencryptaStep {
  public function process() {
    (new AuthorizationInstaller())->install(
      $this->getState()->getDomain(),
      $this->getState()->getWebroot());
  }

}
