<?php

namespace machbarmacher\linear_workflow;

class RecursionChecker {

  protected $jumps = [];

  /**
   * @param $stepName
   * @throws \Exception
   */
  public function notifyJumpTo($stepName) {
    $this->jumps += [$stepName => 0];
    $this->jumps[$stepName] += 1;
    if ($this->jumps[$stepName] > 1) {
      throw new \Exception(sprintf('Recursion: Attempt #%s to repeat step %s.',
        $this->jumps[$stepName], $stepName));
    }
  }

}
