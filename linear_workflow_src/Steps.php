<?php

namespace machbarmacher\linear_workflow;

class Steps {

  /** @var \machbarmacher\linear_workflow\StepInterface[] */
  protected $steps = [];

  /**
   * @param \machbarmacher\linear_workflow\StepInterface $step
   * @return $this
   */
  public function addStep(StepInterface $step) {
    $name = $step->getName();
    $this->steps[$name] = $step;
    return $this;
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
        } catch (Skip $skip) {
          do {
            $stepIndex += 1;
          } while (
            isset($stepNames[$stepIndex]) &&
            in_array($stepNames[$stepIndex], $skip->getStepNames())
          );
        } catch (Finish $finish) {
          $stepIndex = FALSE;
        }
      }
      $stepIndex += 1;
    }
  }

}
