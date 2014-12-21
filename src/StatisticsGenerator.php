<?php

namespace Statgit;

abstract class StatisticsGenerator {

  /**
   * @return an array of generated stats
   */
  abstract function compile();

}
