<?php

namespace machbarmacher\letsencrypta;

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
    //if not done: register email
    $this->leRegister($this->getEmail());
    //authorize domain
    $this->leAuthorize();
    //# place proof
    $this->installAuthorization();
    //check domain
    $this->leCheck();
    //request domain -a domain + initially org details
    // @todo Care for multicertificates.
    $this->leRequest();
    //# install certificate
    $this->installCertificate();
  }

}
