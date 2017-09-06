<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

class InstallCertificate extends AbstractLetsencryptaStep {
  public function process() {
    // @todo Make pluggable.
    $transport = new \Swift_SendmailTransport();
    $mailer = new \Swift_Mailer($transport);
    $message = (new \Swift_Message('Please install SSL certificate'))
      ->setFrom($this->getFromMail())
      ->setCC($this->getWebmasterMail())
      ->setBody(
        sprintf('Please install ssl certificate at %s and %s.',
          $this->getSftp('private'), $this->getSftp('certs')));
    if (!$this->getState()->getInput()->getOption('staging')) {
      $message->setTo('support@freistil.it', 'Freistil Support');
    }
    $result = $mailer->send($message);
  }

  public function getSftp($part) {
    $userAtHost = $this->getUserAtHost();
    $dir = AcmePhpApi::getContainer()->getParameter('app.storage_directory');
    $dir = realpath($dir);
    $domain = $this->getState()->getDomain();
    $result = "sftp://$userAtHost$dir/$part/$domain";
    return $result;
  }

  protected function getWebmasterMail() {
    $domain = $this->getState()->getDomain();
    // Strip subdomains.
    $extract = new LayerShifter\TLDExtract\Extract();
    $registrableDomain = $extract->parse($domain)->getRegistrableDomain();
    $mail = ["webmaster@$registrableDomain" => "$registrableDomain Webmaster"];
    return $mail;
  }

  protected function getFromMail() {
    return [$this->getUserAtHost() => 'MachbarMacher Letsencrypt Bot'];
  }

  /**
   * @return string
   */
  protected function getUserAtHost() {
    $user = getenv('USER') ?: getenv('LOGNAME');
    $host = gethostname();
    $userAtHost = "$user@$host";
    return $userAtHost;
  }

}
