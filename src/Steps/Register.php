<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

class Register extends AbstractLetsencryptaStep {
  public function process() {
    //if not done: register email
    $email = $this->getState()->getInput()->getOption('email') ?:
      'webmaster@' . $this->getState()->getDomain();
    return AcmePhpApi::run('register', [
      'email' => $email,
      'agreement' => TRUE,
    ], $this->getState()->getOutput());
  }

}
