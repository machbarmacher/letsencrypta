<?php

namespace machbarmacher\letsencrypta\Steps;

use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\linear_workflow\AbstractStep;

class Request extends AbstractLetsencryptaStep {
  public function process() {
    // @todo Care for expired auth.
    AcmePhpApi::run('check', [
      'domain' => $this->getState()->getDomain(),
      '--force' => $this->getState()->getInput()->getOption('force'),
      '--alternative-name' => $this->getState()->getAdditionalDomains(),
      // These will not be used by letsencrypt but are required by AcmePhp.
      '--country' => 'n/a',
      '--privince' => 'n/a',
      '--locality' => 'n/a',
      '--organization' => 'n/a',
      '--unit' => 'n/a',
      '--email' => 'n/a',
    ], $this->getState()->getOutput()
    , $this->getState()->isStaging());
  }
}
