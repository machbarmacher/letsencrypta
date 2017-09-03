<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Application;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class AcmePhpApi
 * @package machbarmacher\letsencrypta
 *
 * Collection of api helpers and ugly hacks to circumvent shortcomings of acmephp api.
 */
class AcmePhpApi {

  private static $application;

  /**
   * @return mixed
   */
  public static function getApplication() {
    if (!self::$application) {
      self::$application = new Application();
    }
    return self::$application;
  }

  public static function run($commandName, array $arguments, $output) {
    return self::getApplication()->find($commandName)->run(
      new ArrayInput(array_merge($commandName, $arguments)), $output
    );
  }


}
