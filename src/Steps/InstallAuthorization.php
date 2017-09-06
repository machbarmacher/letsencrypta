<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\Pluggable\AuthorizationInstaller;

class InstallAuthorization extends AbstractLetsencryptaStep {
  public function process() {
    $domain = $this->getState()->getDomain();
    $webroot = $this->getState()->getWebroot();
    (new AuthorizationInstaller())->install($domain, $webroot);
  }

}
