<?php

namespace machbarmacher\letsencrypta;

use machbarmacher\letsencrypta\Steps\Authorize;
use machbarmacher\letsencrypta\Steps\Check;
use machbarmacher\letsencrypta\Steps\InstallAuthorization;
use machbarmacher\letsencrypta\Steps\Plan;
use machbarmacher\letsencrypta\Steps\InstallCertificate;
use machbarmacher\letsencrypta\Steps\Register;
use machbarmacher\letsencrypta\Steps\Request;
use machbarmacher\linear_workflow\Steps;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LetsencryptaCommand extends Command {

  protected function configure() {
    $this
      ->setName('letsencrypta')
      ->setDescription('Do the whole letsencrypt magick.')
      ->setDefinition(
        new InputDefinition(array(
          new InputOption('email', NULL, InputOption::VALUE_OPTIONAL,
            'The mailaddress to register at letsencrypt. Defaults to webmaster@YOURDOMAIN.com'),
        ))
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $domains = $this->getDomains();
    $certificates = $this->getCertificates();

    $newDomains = $this->getUncertifiedDomains($domains, $certificates);
    $expiringDomains = $this->expiringSoon($certificates);
    if (!$newDomains && !$expiringDomains) {
      $output->writeln('Nothing to do.');
      return;
    }

    // @todo Care for multicertificates.
    $state = new State($input, $output);
    $steps = (new Steps())
      ->addStep(new Plan($state))
      ->addStep(new Register($state))
      ->addStep(new Authorize($state))
      ->addStep(new InstallAuthorization($state))
      ->addStep(new Check($state))
      ->addStep(new Request($state))
      ->addStep(new InstallCertificate($state))
      ;
    $steps->process();
  }

  private function getDomains() {
    // @todo Make pluggable.
    // @fixme
  }

  private function getCertificates() {
    // @fixme Get from AcmePhp
  }

  private function expiringSoon($domainCertificates) {
    // @fixme Check AcmePhp certificates.
  }

  private function getUncertifiedDomains($domainRequirements, $domainCertificates) {
    // @fixme Make a diff.
  }

}
