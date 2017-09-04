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
use Symfony\Component\Process\Process;

class LetsencryptaCommand extends Command {

  protected function configure() {
    $this
      ->setName('letsencrypta')
      ->setDescription('Do the whole letsencrypt magick.')
      ->setDefinition(
        new InputDefinition(array(
          new InputOption('email', NULL, InputOption::VALUE_OPTIONAL,
            'The mailaddress to register at letsencrypt. Defaults to webmaster@YOURDOMAIN.com'),
          new InputOption('separate', NULL, InputOption::VALUE_OPTIONAL,
            'Use 1 to force separate certificates for each domain.'),
        ))
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $domains = $this->getDomains();
    $certificatesExisting = AcmePhpApi::getCertificates();
    $makeSeparateCerts = $input->getOption('separate') ?: $certificatesExisting->guessSeparate($domains);

    if ($makeSeparateCerts) {
      $certificatesTodo = [];
      foreach ($domains as $domain) {
        $certificatesTodo[$domain] = [];
      }
    }
    else {
      /** @var \machbarmacher\letsencrypta\Certificate $certificateMatching */
      $certificateMatching = array_values($certificatesExisting->getMultiMatches($domains))[0];
      $domain = $certificateMatching->getDomain();
      $certificatesTodo = [
        $domain => array_diff($domains, [$domain]),
      ];
    }

    foreach ($certificatesTodo as $domain => $alternative) {
      $state = new State($input, $output, $domain, $alternative);
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

  }

  private function getDomains() {
    // @todo Make this pluggable.
    $process = new Process('drush sa --local-only --format=json');
    $process->run();
    if (!$process->isSuccessful()) {
      throw new Exception('Could not get domains from drush.');
    }
    $output = $process->getOutput();
    $aliases = \json_decode($output, TRUE);
    if (JSON_ERROR_NONE !== json_last_error()) {
      throw new \InvalidArgumentException(
        'json_decode error: ' . json_last_error_msg());
    }
    $domains = [];
    foreach ($aliases as $alias) {
      $uri = $alias['uri'];
      $domain = parse_url($uri, PHP_URL_HOST);
      if (FALSE !== strpos($domain, '.')) {
        $domains[$domain] = $domain;
      }
    }
    return $domains;
  }

  private function expiringSoon($domainCertificates) {
    // @fixme Check AcmePhp certificates.
  }

  private function getUncertifiedDomains($domainRequirements, $domainCertificates) {
    // @fixme Make a diff.
  }

}
