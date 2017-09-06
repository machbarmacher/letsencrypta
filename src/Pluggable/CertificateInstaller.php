<?php

namespace machbarmacher\letsencrypta\Pluggable;

use machbarmacher\letsencrypta\AcmePhpApi;

class CertificateInstaller {

  public function install($domain, $email, $test) {
    // @todo Make pluggable.
    $transport = new \Swift_SendmailTransport();
    $mailer = new \Swift_Mailer($transport);
    $message = (new \Swift_Message('Please install SSL certificate'))
      ->setFrom($this->getFromMail())
      ->setCC($email)
      ->setBody(
        sprintf('Please install ssl certificate at %s and %s.',
          $this->getSftp($domain, 'private'), $this->getSftp($domain, 'certs')));
    if (!$test) {
      $message->setTo('support@freistil.it', 'Freistil Support');
    }
    $result = $mailer->send($message);
  }

  public function getSftp($domain, $part) {
    $userAtHost = $this->getUserAtHost();
    $dir = AcmePhpApi::getContainer()->getParameter('app.storage_directory');
    $dir = realpath($dir);
    $result = "sftp://$userAtHost$dir/$part/$domain";
    return $result;
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
