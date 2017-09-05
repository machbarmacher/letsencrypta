<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

class InstallCertificate extends AbstractLetsencryptaStep {
  public function process() {
    // @todo Make pluggable.
    $transport = new \Swift_SendmailTransport();
    $mailer = new \Swift_Mailer($transport);
    $message = (new \Swift_Message('Please install SSL certificate'))
      ->setFrom($this->getState()->getMail())
      //->setTo('support@freistil.it')
      ->setTo('axel.rutz@gmail.com')
      ->setBody(
        printf('Please install ssl certificate at %s and %s.',
          $this->getSftp('private'), $this->getSftp('certs')));
    $result = $mailer->send($message);
  }

  public function getSftp($part) {
    $user = getenv('USER') ?: getenv('LOGNAME');
    $host = gethostname();
    $dir = AcmePhpApi::getContainer()->getParameter('app.storage_directory');
    $dir = realpath($dir);
    $domain = $this->getState()->getDomain();
    $result = "sftp://$user@$host/$dir/$part/$domain";
    return $result;
  }

}
