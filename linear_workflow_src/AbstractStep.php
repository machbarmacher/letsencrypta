<?php

namespace machbarmacher\linear_workflow;

abstract class AbstractStep implements StepInterface {

  abstract public function getName();

  public function isNeeded() {
    return TRUE;
  }

  abstract public function process();

}
