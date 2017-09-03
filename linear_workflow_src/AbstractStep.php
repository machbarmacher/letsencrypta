<?php

namespace machbarmacher\linear_workflow;

abstract class AbstractStep implements StepInterface {

  public function getName() {
    $classWithNamespce = explode('\\', static::class);
    return array_pop($classWithNamespce);
  }

  public function isNeeded() {
    return TRUE;
  }

  abstract public function process();

}
