<?php

namespace machbarmacher\letsencrypta;

use AcmePhp\Cli\Application;
use AcmePhp\Cli\Repository\RepositoryInterface;
use AcmePhp\Ssl\Parser\CertificateParser;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Class AcmePhpApi
 * @package machbarmacher\letsencrypta
 *
 * Collection of helpers and ugly hacks.
 */
class AcmePhpApi {

  /** @var Application */
  private static $application;
  // Note that ContainerBuilder extends Container.
  /** @var \Symfony\Component\DependencyInjection\ContainerBuilder */
  private static $container;

  /**
   * @return \AcmePhp\Cli\Application
   */
  public static function getApplication() {
    if (!self::$application) {
      self::$application = new Application();
    }
    return self::$application;
  }

  /**
   * @param string $commandName
   * @param array $arguments
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   * @param bool $staging
   * @return int
   */
  public static function run($commandName, array $arguments, $output, $staging) {
    $arguments = array_merge([$commandName], $arguments);
    if ($staging) {
      $arguments['--server'] = 'https://acme-staging.api.letsencrypt.org/directory';
    }
    return self::getApplication()->find($commandName)->run(
      new ArrayInput($arguments), $output
    );
  }

  /**
   * @return \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  public static function getContainer() {
    if (!self::$container) {
      self::$container = (new AcmePhpFakeCommand(self::getApplication()))->getContainer();
    }
    return self::$container;
  }

  public static function get($id) {
    return self::getContainer()->get($id);
  }

  /**
   * @return RepositoryInterface
   */
  public static function getRepository() {
    /** @var RepositoryInterface $obj */
    $obj = self::get('repository');
    return $obj;
  }

  /**
   * @return FilesystemInterface
   */
  public static function getStorage() {
    /** @var FilesystemInterface $obj */
    $obj = self::get('repository.master_storage');
    return $obj;
  }

  /**
   * @return CertificateParser
   */
  public static function getCertificateParser() {
    /** @var CertificateParser $obj */
    $obj = self::get('ssl.certificate_parser');
    return $obj;
  }

  public static function getCertificates() {
    // @see \AcmePhp\Cli\Command\StatusCommand
    $repository = AcmePhpApi::getRepository();
    $master = AcmePhpApi::getStorage();
    $certificateParser = AcmePhpApi::getCertificateParser();
    $directories = $master->listContents('certs');
    $certificates = new Certificates();
    foreach ($directories as $directory) {
      if ($directory['type'] !== 'dir') {
        continue;
      }
      $parsedCertificate = $certificateParser->parse($repository->loadDomainCertificate($directory['basename']));
      $domain = $parsedCertificate->getSubject();
      $allDomains = $parsedCertificate->getSubjectAlternativeNames();
      $alternativeDomains = array_diff($allDomains, [$domain]);
      $expiration = $parsedCertificate->getValidTo()->format('U');
      $certificates->add(new Certificate($domain, $alternativeDomains, $expiration));
    }
    return $certificates;
  }

}
