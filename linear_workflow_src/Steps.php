<?php

namespace machbarmacher\linear_workflow;

class Steps {

  /** @var \machbarmacher\linear_workflow\StepInterface[] */
  protected $steps = [];

  public function addStep(StepInterface $step) {
    $name = $step->getName();
    $this->steps[$name] = $step;
  }

  public function process() {
    $stepNames = array_keys($this->steps);
    $stepIndex = 0;
    $recursionChecker = new RecursionChecker();
    while (is_int($stepIndex) && isset($stepNames[$stepIndex])) {
      $stepName = $stepNames[$stepIndex];
      $step = $this->steps[$stepName];
      $isNeeded = $step->isNeeded();
      if ($isNeeded) {
        try {
          $success = $step->process();
          if (!$success) {
            throw new \Exception(sprintf('Step %s did not succeed.', $stepName));
          }
        } catch (JumpTo $jumpTo) {
          $stepIndex = array_search($jumpTo->getDestination(), $stepNames);
          $recursionChecker->notifyJumpTo($stepName);
        }
      }
      $stepIndex += 1;
    }
  }

}
