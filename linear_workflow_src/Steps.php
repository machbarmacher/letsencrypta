<?php

namespace machbarmacher\linear_workflow;

use Symfony\Component\Console\Output\OutputInterface;

class Steps {

  /** @var OutputInterface */
  protected $output;

  /** @var \machbarmacher\linear_workflow\StepInterface[] */
  protected $steps = [];

  /**
   * Steps constructor.
   * @param OutputInterface $output
   */
  public function __construct(OutputInterface $output) {
    $this->output = $output;
  }

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
        $this->output->writeln(sprintf('Running step: %s', $stepName));
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
      else {
        $this->output->writeln(sprintf('Skipping step: %s', $stepName));
      }
      $stepIndex += 1;
    }
  }

}
