<?php

namespace machbarmacher\letsencrypta\Steps;

use AcmePhp\Core\Exception\Server\UnauthorizedServerException;
use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\linear_workflow\JumpTo;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Authorize
 * @package machbarmacher\letsencrypta\Steps
 * @see
 */
class Authorize extends AbstractLetsencryptaStep {
  public function process() {
    $domains = array_merge([$this->getState()->getDomain()],
      $this->getState()->getAdditionalDomains());
    foreach ($domains as $domain) {
      $this->authorize($domain);
    }
  }

  private function authorize($domain) {
    try {
      AcmePhpApi::run('authorize', [
        'domain' => $this->getState()->getDomain(),
        '--solver' => 'http',
      ], $this->getState()->getOutput()
        , $this->getState()->isTest());
    } catch (UnauthorizedServerException $e) {
      $this->getState()->getOutput()->writeln(sprintf(
        'Authorize exception: %s %s', get_class($e), $e->getMessage()),
        OutputInterface::VERBOSITY_VERBOSE);
      throw new JumpTo('Register');
    }
  }
}
