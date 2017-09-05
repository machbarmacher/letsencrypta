<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Application;
use AcmePhp\Cli\Command\AbstractCommand;

/**
 * Class AcmePhpFakeCommand
 * @package machbarmacher\letsencrypta
 *
 * @see \machbarmacher\letsencrypta\AcmePhpApi::getContainer
 */
class AcmePhpFakeCommand extends AbstractCommand {
  public function __construct(Application $application, $name = NULL) {
    parent::__construct($name);
    $this->setApplication($application);
  }

  public function getContainer() {
    return parent::getContainer();
  }
}
