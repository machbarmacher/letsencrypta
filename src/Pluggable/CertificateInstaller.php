<?php

namespace machbarmacher\letsencrypta\Pluggable;

use machbarmacher\letsencrypta\AcmePhpApi;

class CertificateInstaller {

  public function install($domain, $email, $test) {
    // @todo Make pluggable.
    $transport = new \Swift_SendmailTransport();
    $mailer = new \Swift_Mailer($transport);
    $sftp = $this->getSftp();
    $message = (new \Swift_Message('Please install SSL certificate'))
      ->setFrom($email)
      ->setCC($email)
      ->setBody(
        sprintf(<<<EOD
This is your friendly MachbarMacher certbot.
I gonna spam you until you adopt me on your server.
Please install the ssl certificate at:
%s
%s
OR
%s

EOD
,
          "$sftp/private/$domain/private.pem",
          "$sftp/certs/$domain/fullchain.pem",
          "$sftp/certs/$domain/combined.pem"
        ));
    if (!$test) {
      $message->setTo('support@freistil.it', 'Freistil Support');
    }
    $result = $mailer->send($message);
  }

  public function getSftp() {
    $userAtHost = $this->getUserAtHost();
    $dir = AcmePhpApi::getContainer()->getParameter('app.storage_directory');
    $dir = realpath($dir);
    $result = "sftp://$userAtHost$dir";
    return $result;
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
