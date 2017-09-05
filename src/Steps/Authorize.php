<?php

namespace machbarmacher\letsencrypta\Steps;

use AcmePhp\Core\Exception\Server\UnauthorizedServerException;
use machbarmacher\letsencrypta\AcmePhpApi;
use machbarmacher\linear_workflow\JumpTo;

/**
 * Class Authorize
 * @package machbarmacher\letsencrypta\Steps
 * @see
 */
class Authorize extends AbstractLetsencryptaStep {
  public function process() {
    try {
      $result = AcmePhpApi::run('authorize', [
        'domain' => $this->getState()->getDomain(),
        '--solver' => 'http',
      ], $this->getState()->getOutput()
        , $this->getState()->isStaging());
    } catch (UnauthorizedServerException $e) {
      throw new JumpTo('Register');
    }
    return $result;
  }
}
