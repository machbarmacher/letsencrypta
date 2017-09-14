<?php

namespace machbarmacher\letsencrypta\Pluggable;

use AcmePhp\Core\Challenge\Http\HttpDataExtractor;
use machbarmacher\letsencrypta\AcmePhpApi;
use Symfony\Component\Filesystem\Filesystem;

class AuthorizationInstaller {

  public function install($domain, $webroot) {
    // @todo Make pluggable.
    list($fileContent, $basename) = $this->getAuthorization($domain);

    // Symlink without target dir? Create it!
    $dir = "$webroot/.well-known/acme-challenge";
    $fs = new Filesystem();
    if (
      is_link($dir)
      // @todo Upstream this forked method.
      && ($linkTarget = $this->readlink($dir, TRUE))
      && !file_exists($linkTarget)
    ) {
      $fs->mkdir($linkTarget);
    }
    $fileName = $dir . "/$basename";
    $fs->dumpFile($fileName, $fileContent);
  }

  public function remove($domain, $webroot) {
    list($fileContent, $basename) = $this->getAuthorization($domain);
    $dir = "$webroot/.well-known/acme-challenge";
    $fileName = $dir . "/$basename";
    $fs = new Filesystem();
    $fs->remove($fileName);
  }

  /**
   * @param $domain
   * @return array
   */
  protected function getAuthorization($domain) {
    $extractor = new HttpDataExtractor();
    $repository = AcmePhpApi::getRepository();

    $authorizationChallenge = $repository->loadDomainAuthorizationChallenge($domain);
    $url = $extractor->getCheckUrl($authorizationChallenge);
    $fileContent = $extractor->getCheckContent($authorizationChallenge);
    $basename = basename($url);
    return array($fileContent, $basename);
  }

  /**
   * Resolves links in paths.
   *
   * With $canonicalize = false (default)
   *      - if $path does not exist or is not a link, returns null
   *      - if $path is a link, returns the next direct target of the link without considering the existence of the target
   *
   * With $canonicalize = true
   *      - if $path does not exist, returns null
   *      - if $path exists, returns its absolute fully resolved final version
   *
   * @param string $path         A filesystem path
   * @param bool   $canonicalize Whether or not to return a canonicalized path
   *
   * @return string|null
   */
  public function readlink($path, $canonicalize = false)
  {
    $fs = new Filesystem();
    if (!$canonicalize && !is_link($path)) {
      return;
    }

    if ($canonicalize) {
      if (!$fs->exists($path)) {
        return;
      }

      $dir = dirname($path);
      $path = readlink($path);

      if (!$fs->isAbsolutePath($path)) {
        $path = $dir . DIRECTORY_SEPARATOR . $path;
      }
      return realpath($path);
    }

    return readlink($path);
  }

}
