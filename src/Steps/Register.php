<?php

namespace machbarmacher\letsencrypta\Steps;

class Register extends AbstractLetsencryptaStep {
  public function process() {
    //if not done: register email
    $email = $this->getState()->getInput()->getOption('email') ?:
      'webmaster@' . $this->getState()->getDomain();
    return $this->getState()->acmePhpRun('register', [
      'email' => $email,
      'agreement' => TRUE,
    ]);
  }

}
