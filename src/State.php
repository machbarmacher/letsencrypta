<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Application as AcmePhpApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class State {
  /** @var \Symfony\Component\Console\Command\Command */
  private $command;
  /** @var \Symfony\Component\Console\Input\InputInterface */
  private $input;
  /** @var  \Symfony\Component\Console\Output\OutputInterface */
  private $output;
  /** @var string */
  private $domain;
  /** @var string[] */
  private $additionalDomains;
  /** @var string */
  private $webroot;
  /** @var bool */
  private $test;
  /** @var string */
  private $email;

  /**
   * State constructor.
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @param string $domain
   * @param string[] $additionalDomains
   * @param string $webroot
   * @param bool $test
   */
  public function __construct(Command $command, InputInterface $input, OutputInterface $output, $domain, $additionalDomains, $webroot, $test, $email) {
    $this->command = $command;
    $this->input = $input;
    $this->output = $output;
    $this->domain = $domain;
    $this->additionalDomains = $additionalDomains;
    $this->webroot = $webroot;
    $this->test = $test;
    $this->email = $email;
  }

  /**
   * @return \Symfony\Component\Console\Command\Command
   */
  public function getCommand() {
    return $this->command;
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

  public function getWebroot() {
    return $this->webroot;
  }

  /**
   * @return bool
   */
  public function isTest() {
    return $this->test;
  }

  /**
   * @return string
   */
  public function getEmail() {
    return $this->email;
  }


}
