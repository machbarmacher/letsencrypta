<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\linear_workflow\Skip;

class Plan extends AbstractLetsencryptaStep {
  public function process() {
    $domain = $this->getState()->getDomain();
    $skip = [];
    // Skip registration if we have an acount key pair.
    // This might break if keys are generated but not registered.
    // @todo Patch AcmePhp to create a registration artefact.
    $repository = AcmePhpApi::getRepository();
    if ($repository->hasAccountKeyPair()) {
      $skip[] = 'Register';
    }
    // Skip authorization if we have a challenge.
    // @todo Consider authorizing alwoys.
    if($repository->hasDomainAuthorizationChallenge($domain)) {
      $skip[] = 'Authorize';
    }
    // InstallAuthorization is cheap, don't check this.
    // Check does not leave an artefact, don't check this.
    // Finally, Request and InstallCertificate must run always.
    if ($skip) {
      throw new Skip($skip);
    }
  }

}
