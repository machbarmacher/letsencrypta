<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\letsencrypta\State;

class Register extends AbstractLetsencryptaStep {
  /**
   * @see \AcmePhp\Cli\Command\RegisterCommand
   */
  public function process() {
    $email = $this->getState()->getEmail() ?:
      'webmaster@' . $this->getState()->getDomain();
    AcmePhpApi::run('register', [
      'email' => $email,
    ], $this->getState()->getOutput()
    , $this->getState()->isTest());
  }

}
