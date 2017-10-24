<?php

namespace machbarmacher\letsencrypta\Command;

use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\letsencrypta\State;
use machbarmacher\letsencrypta\Steps\Authorize;
use machbarmacher\letsencrypta\Steps\Check;
use machbarmacher\letsencrypta\Steps\InstallAuthorization;
use machbarmacher\letsencrypta\Steps\InstallCertificate;
use machbarmacher\letsencrypta\Steps\Plan;
use machbarmacher\letsencrypta\Steps\Register;
use machbarmacher\letsencrypta\Steps\RemoveAuthorization;
use machbarmacher\letsencrypta\Steps\Request;
use machbarmacher\linear_workflow\Steps;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
          new InputArgument('domain', InputArgument::REQUIRED,
            'The certificate domain.'),
          new InputArgument('alternative', InputArgument::IS_ARRAY,
            'Alternative domains.'),
          new InputOption('webroot', NULL, InputOption::VALUE_REQUIRED,
            'The webroot to install the authorization on.'),
          new InputOption('email', NULL, InputOption::VALUE_OPTIONAL,
            'The mailaddress to register at letsencrypt and use as from address. Defaults to webmaster@YOURDOMAIN.com'),
          new InputOption('cert-mailto', NULL, InputOption::VALUE_OPTIONAL,
            'The mailaddress to mail the certificate install instructions to.'),
          new InputOption('test', NULL, InputOption::VALUE_NONE,
            'Use letsencrypt staging server for testing. Also omit cert mail recepint.'),
          new InputOption('force', 'f', InputOption::VALUE_NONE,
            'Whether to force renewal or not (by default, renewal will be done only if the certificate expire in less than 2 weeks)'),
        ))
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    // @todo replace $output->writeln with symfony styling.
    $domain = $input->getArgument('domain');
    $alternative = (array)$input->getArgument('alternative');
    $alternative = array_diff($alternative, [$domain]);
    $plusAlternative = $alternative ? sprintf(' + %s', implode(', ', $alternative)) : '';
    $webroot = $input->getOption('webroot');

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
      return;
    }

    $email = $input->getOption('email') ?: $this->getWebmasterMail($domain);
    // @todo Put all config in here so others need not query input options.
    $state = new State($this, $input, $output, $domain, $alternative, $webroot, $input->getOption('test'), $email);
    // @todo Package out the linear workflow engine.
    $steps = (new Steps($output))
      ->addStep(new Plan($state))
      ->addStep(new Register($state))
      ->addStep(new Authorize($state))
      ->addStep(new InstallAuthorization($state))
      ->addStep(new Check($state))
      ->addStep(new RemoveAuthorization($state))
      ->addStep(new Request($state))
      ->addStep(new InstallCertificate($state))
    ;
    $steps->process();

  }

  protected function getWebmasterMail($domain) {
    // Strip subdomains.
    $extract = new \LayerShifter\TLDExtract\Extract();
    $registrableDomain = $extract->parse($domain)->getRegistrableDomain();
    $mail = "webmaster@$registrableDomain";
    return $mail;
  }

  protected function symdiff(array $a, array $b) {
    return array_unique(array_merge(
      array_diff($a, $b),
      array_diff($b, $a)
    ));
  }

}
