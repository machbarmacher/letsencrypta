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
          $destination = $jumpTo->getDestination();
          $this->output->writeln(sprintf('Jumping to step: %s', $destination));
          $stepIndex = array_search($destination, $stepNames);
          $recursionChecker->notifyJumpTo($stepName);
        } catch (Skip $skip) {
          do {
            $stepIndex += 1;
            $stepName = isset($stepNames[$stepIndex]) ?
              $stepNames[$stepIndex] : NULL;
            $this->output->writeln(sprintf('Skipping step: %s', $stepName));
          } while (in_array($stepName, $skip->getStepNames(), TRUE));
        } catch (Finish $finish) {
          $stepIndex = FALSE;
        }
      }
      else {
        $this->output->writeln(sprintf('Skipping unnecessary step: %s', $stepName));
      }
      $stepIndex += 1;
    }
  }

}
