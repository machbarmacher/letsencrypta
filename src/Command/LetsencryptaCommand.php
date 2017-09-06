<?php

namespace machbarmacher\letsencrypta\Command;

use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\letsencrypta\Exception;
use machbarmacher\letsencrypta\State;
use machbarmacher\letsencrypta\Steps\Authorize;
use machbarmacher\letsencrypta\Steps\Check;
use machbarmacher\letsencrypta\Steps\InstallAuthorization;
use machbarmacher\letsencrypta\Steps\InstallCertificate;
use machbarmacher\letsencrypta\Steps\Plan;
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
          new InputOption('reregister', NULL, InputOption::VALUE_OPTIONAL,
            'Force re-registration.'),
          new InputOption('staging', NULL, InputOption::VALUE_OPTIONAL,
            'Use letsencrypt staging server for testing.'),
          new InputOption('force', 'f', InputOption::VALUE_NONE,
            'Whether to force renewal or not (by default, renewal will be done only if the certificate expire in less than 2 weeks)'),
        ))
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $domainWebroots = $this->getDomainWebroots();
    if (!$domainWebroots) {
      $output->writeln('No domains found.');
      return;
    }
    $domains = array_keys($domainWebroots);
    $certificatesExistingContainer = AcmePhpApi::getCertificatesContainer();
    $makeSeparateCerts = $input->getOption('separate') ?: $certificatesExistingContainer->guessSeparate($domains);

    $certificatesTodo = [];
    if ($makeSeparateCerts) {
      foreach ($domains as $domain) {
        $certificate = $certificatesExistingContainer->get($domain);
        if (
          !$certificate
          || $certificate->isExpiring()
          || $input->getOption('force')
        ) {
          $certificatesTodo[$domain] = [];
          if (!$certificate) {
            $output->writeln("Create new certificate for $domain.");
          }
          elseif ($certificate->isExpiring()) {
            $output->writeln("Create expiring certificate for $domain.");
          }
          else {
            $output->writeln("Force create certificate for $domain.");
          }
        }
      }
    }
    else {
      /** @var \machbarmacher\letsencrypta\Certificate $certificate */
      $certificatesMatching = array_values($certificatesExistingContainer->getMultiMatches($domains));
      if ($certificatesMatching) {
        // Assume only one. This breaks in weird situations.
        $certificate = $certificatesMatching[0];
        $domain = $certificate->getDomain();
        $additionalDomains = array_diff($domains, [$domain]);
        $additional = $additionalDomains ? ' including ' . implode(',', $additionalDomains) : '';
        if (
          $certificate->isExpiring()
          || $input->getOption('force')
        ) {
          if ($certificate->isExpiring()) {
            $output->writeln("Create expiring certificate for $domain$additional.");
          }
          else {
            $output->writeln("Force create certificate for $domain$additional.");
          }
        }
        else {
          $date = date('Y-m-d', $certificate->getExpiration());
          $output->writeln("Skip creating certificate for $domain$additional, expiring on $date.");
          unset($domain);
        }
      }
      else {
        $domain = $domains[0];
        $additionalDomains = array_diff($domains, [$domain]);
        $additional = $additionalDomains ? ' including ' . implode(',', $additionalDomains) : '';
        $output->writeln("Create new certificate for $domain$additional.");
      }
      if (isset($domain)) {
        $certificatesTodo[$domain] = $additionalDomains;
      }
    }

    foreach ($certificatesTodo as $domain => $alternative) {
      $output->writeln(sprintf('Certifying domain %s%s', $domain,
        $alternative ? sprintf(' + %s', implode(', ', $alternative)) : ''));
      $state = new State($this, $input, $output, $domain, $alternative, $domainWebroots[$domain], $input->getOption('staging'));
      $steps = (new Steps($output))
        ->addStep(new Plan($state, $input->getOption('reregister')))
        ->addStep(new Register($state))
        ->addStep(new Authorize($state))
        ->addStep(new InstallAuthorization($state))
        ->addStep(new Check($state))
        ->addStep(new Request($state))
        ->addStep(new InstallCertificate($state))
        // @todo Clean up challenges.
      ;
      $steps->process();
    }

  }

  private function getDomainWebroots() {
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
        sprintf("Jsondecode error: %s\n%s\nEOD", json_last_error_msg(), $output));
    }
    $domainWebroots = [];
    foreach ($aliases as $alias) {
      // drush sa --local-only seems not to filter reliably.
      if (isset($alias['remote-host'])) {
        continue;
      }
      $uri = $alias['uri'];
      $root = $alias['root'];
      $domain = parse_url($uri, PHP_URL_HOST);
      if (FALSE !== strpos($domain, '.')) {
        $domainWebroots[$domain] = $root;
      }
    }
    return $domainWebroots;
  }

}
