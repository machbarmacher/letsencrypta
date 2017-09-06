<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\linear_workflow\AbstractStep;

class Request extends AbstractLetsencryptaStep {
  public function process() {
    // @todo Care for expired auth.
    AcmePhpApi::run('request', [
      'domain' => $this->getState()->getDomain(),
      '--force' => 'FORCE',
      '--alternative-name' => $this->getState()->getAdditionalDomains(),
      // These will not be used by letsencrypt but are required by AcmePhp.
      '--country' => 'US',
      '--province' => 'bar',
      '--locality' => 'baz',
      '--organization' => 'boo',
      '--unit' => 'quoo',
      '--email' => 'none@example.com',
    ], $this->getState()->getOutput()
    , $this->getState()->isStaging());
  }
}
