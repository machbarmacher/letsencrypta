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

  protected function readlink($path, $canonicalize = FALSE)
  {
    if (!is_link($path)) {
      return NULL;
    }

    $result = readlink($path);

    if ($canonicalize && !$this->pathIsAbsolute($result)) {
      $dir = dirname($path);
      $result = "$dir/$result";
      $result = $this->canonicalize($result);
    }

    return $result;
  }

  /**
   * Canonicalize a path.
   *
   * Remove . and .. without using realpath(), as that does
   * not work on nonexisting targets.
   *
   * @param $path
   * @return string
   */
  public function canonicalize($path) {
    $parts = explode(DIRECTORY_SEPARATOR, $path);
    $result = [];
    foreach ($parts as $part) {
      if ('..' === $part && $result) {
        array_pop($result);
      }
      elseif ('.' !== $part) {
        $result[] = $part;
      }
    }
    return implode(DIRECTORY_SEPARATOR, $result);
  }

  /**
   * @param $path
   * @return int
   */
  protected function pathIsAbsolute($path) {
    return strspn($path, '/', 0, 1);
  }

}
