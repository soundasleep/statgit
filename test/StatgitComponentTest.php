<?php

/**
 * Test the Statgit compent itself.
 */
class StatgitComponentTest extends \ComponentTests\ComponentTest {

  function getRoots() {
    return array(__DIR__ . "/..");
  }

  /**
   * May be extended by child classes to define a list of path
   * names that will be excluded by {@link #iterateOver()}.
   */
  function getExcludes() {
    $result = array();
    foreach (file(__DIR__ . "/../.gitignore") as $row) {
      $result[] = "/" . trim($row);
    }
    return $result;
  }

}
