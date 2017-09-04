<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

class Register extends AbstractLetsencryptaStep {
  /**
   * @return int
   * @see \AcmePhp\Cli\Command\RegisterCommand
   */
  public function process() {
    $email = $this->getState()->getInput()->getOption('email') ?:
      'webmaster@' . $this->getState()->getDomain();
    return AcmePhpApi::run('register', [
      'email' => $email,
      'agreement' => TRUE,
    ], $this->getState()->getOutput());
  }

}
