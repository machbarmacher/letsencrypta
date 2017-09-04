<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\letsencrypta\State;

class Register extends AbstractLetsencryptaStep {
  /** @var bool */
  private $force;

  public function __construct(State $state, $force) {
    parent::__construct($state);
    $this->force = $force;
  }


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

  public function isNeeded() {
    if ($this->force) {
      return TRUE;
    }
    // Skip registration if we have an acount key pair.
    // This might break if keys are generated but not registered.
    // @todo Patch AcmePhp to create a registration artefact.
    $repository = AcmePhpApi::getRepository();
    $done = $repository->hasAccountKeyPair();
    return !$done;
  }

}
