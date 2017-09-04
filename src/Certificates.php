<?php

namespace machbarmacher\letsencrypta;

class Certificates {

  /** @var \machbarmacher\letsencrypta\Certificate[] */
  private $certificates = [];

  public function add(Certificate $certificate) {
    $this->certificates[$certificate->getDomain()] = $certificate;
  }

  /**
   * @param string $domain
   * @return \machbarmacher\letsencrypta\Certificate|null
   */
  public function get($domain) {
    return isset($this->certificates[$domain]) ? $this->certificates[$domain] : NULL;
  }

  /**
   * @param string[] $domains
   * @return Certificate[]
   */
  public function getMultiMatches($domains) {
    $matches = [];
    foreach ($this->certificates as $certificate) {
      if (in_array($certificate->getDomain(), $domains) || array_intersect($domains, $certificate->getAlternative())) {
        $matches[$certificate->getDomain()] = $certificate;
      }
    }
    return $matches;
  }

  /**
   * @param string $domain
   * @return Certificate[]
   */
  public function getMatches($domain) {
    return $this->getMultiMatches([$domain]);
  }

  /**
   * @param string[] $domains
   * @return bool
   */
  public function guessSeparate($domains) {
    if (count($domains) < 2) {
      return FALSE;
    }
    $priorMatches = [];
    foreach ($domains as $domain) {
      $newMatches = $this->getMatches($domain);
      if (array_intersect_key($priorMatches, $newMatches)) {
        // Found 2 domains with same certificate, so not separate.
        return FALSE;
      }
      $priorMatches += $newMatches;
    }
    return TRUE;
  }
}
