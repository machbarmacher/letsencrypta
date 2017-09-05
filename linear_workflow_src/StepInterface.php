<?php

namespace machbarmacher\linear_workflow;

interface StepInterface {

  /**
   * @return string
   */
  public function getName();

  /**
   * @return bool
   */
  public function isNeeded();

  /**
   * @throws \machbarmacher\linear_workflow\JumpTo
   * @throws \machbarmacher\linear_workflow\Skip
   * @throws \machbarmacher\linear_workflow\Finish
   */
  public function process();

}
