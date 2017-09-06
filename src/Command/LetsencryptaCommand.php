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
          new InputOption('webroot', NULL, InputOption::VALUE_REQUIRED,
            'The site webroot.'),
          new InputOption('domain', NULL, InputOption::VALUE_REQUIRED,
            'The certificate domain.'),
          new InputOption('alternative', NULL, InputOption::VALUE_OPTIONAL,
            'Alternative domains.'),
          new InputOption('test', NULL, InputOption::VALUE_OPTIONAL,
            'Use letsencrypt staging server for testing.'),
          new InputOption('force', 'f', InputOption::VALUE_NONE,
            'Whether to force renewal or not (by default, renewal will be done only if the certificate expire in less than 2 weeks)'),
        ))
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $webroot = $input->getOption('webroot');
    $domain = $input->getOption('domain');
    $alternative = (array)$input->getOption('alternative');
    $alternative = array_diff($alternative, [$domain]);
    $plusAlternative = $alternative ? sprintf(' + %s', implode(', ', $alternative)) : '';

    $certificate = AcmePhpApi::getCertificates()->get($domain);
    if (!$certificate) {
      $output->writeln("Create new certificate for $domain$plusAlternative.");
    }
    elseif ($certificate->isExpiring()) {
      $output->writeln("Create expiring certificate for $domain$plusAlternative.");
    }
    elseif ($this->symdiff($alternative, $certificate->getAlternative())) {
      $output->writeln("Create certificate due to change of additional domains for $domain$plusAlternative.");
    }
    elseif ($input->getOption('force')) {
      $output->writeln("Force create certificate for $domain$plusAlternative.");
    }
    else {
      $date = date('Y-m-d', $certificate->getExpiration());
      $output->writeln("Nothing to do for $domain expiring on $date.", OutputInterface::VERBOSITY_VERBOSE);
    }

    $state = new State($this, $input, $output, $domain, $alternative, $webroot, $input->getOption('test'), $input->getOption('email'));
    $steps = (new Steps($output))
      ->addStep(new Plan($state))
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

  protected function symdiff(array $a, array $b) {
    return array_unique(array_merge(
      array_diff($a, $b),
      array_diff($b, $a)
    ));
  }

}
