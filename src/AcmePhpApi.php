<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Application as AcmePhpApplication;
use Symfony\Component\Console\Input\ArrayInput;

class AcmePhpApi {

  private static $acmePhpApplication;

  /**
   * @return mixed
   */
  public static function getAcmePhpApplication() {
    if (!static::$acmePhpApplication) {
      static::$acmePhpApplication = new AcmePhpApplication();
    }
    return self::$acmePhpApplication;
  }

  public static function acmePhpRun($commandName, array $arguments, $output) {
    return static::getAcmePhpApplication()->find($commandName)->run(
      new ArrayInput(array_merge($commandName, $arguments)), $output
    );
  }

}
