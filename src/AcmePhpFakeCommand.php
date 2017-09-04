<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Command\AbstractCommand;

/**
 * Class AcmePhpFakeCommand
 * @package machbarmacher\letsencrypta
 *
 * @see \machbarmacher\letsencrypta\AcmePhpApi::getContainer
 */
class AcmePhpFakeCommand extends AbstractCommand {
  public function getContainer() {
    return parent::getContainer();
  }
}
