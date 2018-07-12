<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;

class InstallCertificate extends AbstractLetsencryptaStep {
  public function process() {
    // @todo Emit symfony event.

    $mailTo = $this->getState()->getCertMailto();
    if ($mailTo) {
      $webmasterMail = $this->getState()->getEmail();

      $domain = $this->getState()->getDomain();
      $isTest = $this->getState()->isTest();

      $transport = new \Swift_SendmailTransport();
      $mailer = new \Swift_Mailer($transport);
      $dir = $this->getDir();
      $message = (new \Swift_Message('Please install SSL certificate (for maybe multiple SANs!)'))
        ->setTo($mailTo)
        ->setFrom($webmasterMail)
        ->setCC($webmasterMail)
        ->setBody(
          sprintf(<<<EOD
This is your friendly MachbarMacher certbot running on:
User: %s
Host: %s
Please install the %s ssl certificate:
Private key:
%s
Certificate:
%s
Trust chain:
%s

Note: This may apply to multiple SANs.

Thank you!
EOD
            ,
            $this->getUser(),
            $this->getHost(),
            $isTest ? 'invalid test' : 'valid live',
            "$dir/private/$domain/private.pem",
            "$dir/certs/$domain/cert.pem",
            "$dir/certs/$domain/chain.pem"
          ));
      $mailer->send($message, $failedRecipients);
      if ($failedRecipients) {
        $this->getState()->getOutput()->writeln(
          sprintf('Install mail failed for: %s', implode(', ', $failedRecipients))
        );
      }
    }

  }

  public function getDir() {
    $dir = AcmePhpApi::getContainer()->getParameter('app.storage_directory');
    $dir = realpath($dir);
    return $dir;
  }

  protected function getUser() {
    return getenv('USER') ?: getenv('LOGNAME');
  }

  protected function getHost() {
    return gethostname();
  }

}
