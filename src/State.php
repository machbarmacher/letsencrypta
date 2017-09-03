<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Application as AcmePhpApplication;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class State {
  /** @var \Symfony\Component\Console\Input\InputInterface */
  private $input;
  /** @var  \Symfony\Component\Console\Output\OutputInterface */
  private $output;
  /** @var string */
  private $domain;
  /** @var string[] */
  private $additionalDomains;

  /** @var \AcmePhp\Cli\Application */
  private $acmePhpApplication;

  /**
   * State constructor.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @param string $domain
   * @param string[] $additionalDomains
   */
  public function __construct(InputInterface $input, OutputInterface $output, $domain, $additionalDomains) {
    $this->input = $input;
    $this->output = $output;
    $this->domain = $domain;
    $this->additionalDomains = $additionalDomains;
    $this->acmePhpApplication = new AcmePhpApplication();
  }

  public function acmePhpRun($commandName, array $arguments) {
    return $this->acmePhpApplication->find($commandName)->run(
      new ArrayInput(array_merge($commandName, $arguments)),
      $this->getOutput()
    );
  }

  /**
   * @return \Symfony\Component\Console\Input\InputInterface
   */
  public function getInput() {
    return $this->input;
  }

  /**
   * @return \Symfony\Component\Console\Output\OutputInterface
   */
  public function getOutput() {
    return $this->output;
  }

  /**
   * @return string
   */
  public function getDomain() {
    return $this->domain;
  }

  /**
   * @return string[]
   */
  public function getAdditionalDomains() {
    return $this->additionalDomains;
  }

}
