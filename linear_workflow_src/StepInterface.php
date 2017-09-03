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
   * @return bool $success
   * @throws \machbarmacher\linear_workflow\JumpTo
   */
  public function process();

}
