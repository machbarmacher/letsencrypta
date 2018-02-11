<?php

namespace machbarmacher\letsencrypta;

class Certificate {
  /** @var string */
  private $domain;
  /** @var string[] */
  private $alternative;
  /** @var int|null */
  private $expiration;

  /**
   * Certificate constructor.
   * @param string $domain
   * @param string[] $alternative
   * @param int $expiration
   */
  public function __construct($domain, array $alternative, $expiration = NULL) {
    $this->domain = $domain;
    $this->alternative = $alternative;
    $this->expiration = $expiration;
  }

  /**
   * @return string
   */
  public function getDomain() {
    return $this->domain;
  }

  /**
   * @return string[]
   */
  public function getAlternative() {
    return $this->alternative;
  }

  /**
   * @return int
   */
  public function getExpiration() {
    return $this->expiration;
  }

  public function isExpiring() {
    // LE sends its first mail on T-20d.
    return $this->expiration - time() < 22 * 24 * 3600;
  }

}
