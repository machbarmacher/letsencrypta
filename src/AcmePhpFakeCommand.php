<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Application;
use AcmePhp\Cli\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AcmePhpFakeCommand
 * @package machbarmacher\letsencrypta
 *
 * @see \machbarmacher\letsencrypta\AcmePhpApi::getContainer
 */
class AcmePhpFakeCommand extends AbstractCommand {
  public function __construct(Application $application, InputInterface $input, OutputInterface $output) {
    parent::__construct('AcmePhpFakeCommand');
    $this->setApplication($application);
    $this->initialize($input, $output);
  }

  public function getContainer() {
    return parent::getContainer();
  }
}
