<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Application as AcmePhpApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LetsencryptaCommand extends Command {

  /** @var \AcmePhp\Cli\Application */
  protected $acmePhpApplication;

  public function __construct($name = NULL) {
    parent::__construct($name);
    $this->acmePhpApplication = new AcmePhpApplication();
  }

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
    //# check if domains changed OR cert expires soon, if not bail out
    // @todo Care for multicertificates.
    $domainRequirements = $this->getDomains();
    $domainCertificates = $this->getDomainCertificates();
    $newDomains = $this->getUncertifiedDomains($domainRequirements, $domainCertificates);
    $expiringDomains = $this->expiringSoon($domainCertificates);
    if (!$newDomains && !$expiringDomains) {
      // @todo Log something.
      return;
    }
    if ($this->isFirstRun()) {
      //if not done: register email
      $email = $input->getOption('email') ?: $this->getDefaultEmail($domainRequirements);
      $this->leRegister($email);

      foreach ($domainRequirements as $domainRequirement) {
        //authorize domains
        $this->leAuthorize($domainRequirement);
        //# place proof
        $this->installAuthorization();
        //check domain
        $this->leCheck($domainRequirement);
      }
    }
    //request domains
    // @todo Care for multicertificates.
    // @todo Care for expired auth.
    $this->leRequest($domainRequirements, $input->getOption('force'));
    //# install certificate
    $this->installCertificate();
  }

  protected function acmePhpRun($commandName, array $arguments, OutputInterface $output) {
    return $this->acmePhpApplication->find($commandName)->run(
      new ArrayInput(array_merge($commandName, $arguments)),
      $output
    );
  }

  protected function isFirstRun() {
    return TRUE; // @fixme
  }

  private function getDefaultEmail($domains) {
    // @todo Consider identifying the default domain somehow.
    $domain = array_values($domains)[0];
    return "webmaster@$domain";
  }

  private function getDomains() {
    // @todo Make pluggable.
    // @fixme
  }

  private function getDomainCertificates() {
    // @fixme Get from AcmePhp
  }

  private function expiringSoon($domainCertificates) {
    // @fixme Check AcmePhp certificates.
  }

  private function getUncertifiedDomains($domainRequirements, $domainCertificates) {
    // @fixme Make a diff.
  }

  private function leRegister($email = NULL) {
    return $this->acmePhpRun('register', [
      'email' => $email,
      'agreement' => TRUE,
    ], new OutputInterface());
  }

  private function leAuthorize($domain) {
    return $this->acmePhpRun('register', [
      'domain' => $domain,
      '--solver' => 'http',
    ], new OutputInterface());
  }

  private function installAuthorization() {
    // @todo Make pluggable.
    // @fixme
  }

  private function leCheck($domain) {
    return $this->acmePhpRun('check', [
      'domain' => $domain,
      '--solver' => 'http',
    ], new OutputInterface());
  }

  private function leRequest($domains, $force) {
    return $this->acmePhpRun('check', [
      'domain' => array_values($domains)[0],
      '--force' => '$force',
      '--alternative-name' => array_slice($domains,1),
      // These will not be used by letsencrypt but are required by AcmePhp.
      '--country' => 'n/a',
      '--privince' => 'n/a',
      '--locality' => 'n/a',
      '--organization' => 'n/a',
      '--unit' => 'n/a',
      '--email' => 'n/a',
    ], new OutputInterface());
  }

  private function installCertificate() {
    // @todo Make pluggable.
    // @fixme
  }

}
