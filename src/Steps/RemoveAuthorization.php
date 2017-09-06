<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\Pluggable\AuthorizationInstaller;

class RemoveAuthorization extends AbstractLetsencryptaStep {
  public function process() {
    (new AuthorizationInstaller())->remove(
      $this->getState()->getDomain(),
      $this->getState()->getWebroot());
  }

}
