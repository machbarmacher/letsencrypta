<?php

namespace machbarmacher\letsencrypta\Steps;

use AcmePhp\Core\Challenge\Http\HttpDataExtractor;
use machbarmacher\letsencrypta\AcmePhpApi;
use Symfony\Component\Filesystem\Filesystem;

class InstallAuthorization extends AbstractLetsencryptaStep {
  public function process() {
    // @todo Make pluggable.
    $extractor = new HttpDataExtractor();
    $fs = new Filesystem();
    $repository = AcmePhpApi::getRepository();

    $domain = $this->getState()->getDomain();
    $authorizationChallenge = $repository->loadDomainAuthorizationChallenge($domain);
    $url = $extractor->getCheckUrl($authorizationChallenge);
    $fileContent = $extractor->getCheckContent($authorizationChallenge);
    $basename = basename($url);
    $webroot = $this->getState()->getWebroot();
    $fileName = "$webroot/.well-known/acme-challenge/$basename";
    $fs->dumpFile($fileName, $fileContent);
  }
}
