<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

class InstallCertificate extends AbstractLetsencryptaStep {
  public function process() {
    (new \machbarmacher\letsencrypta\Pluggable\CertificateInstaller())
      ->install($this->getState()->getDomain(),
        $this->getState()->getEmail(),
        $this->getState()->isTest());
  }

}
