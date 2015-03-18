<?php

namespace Statgit;

/**
 * Generate some basic composer.json and composer.lock stats
 */
class ComposerStatsFinder extends \PhpParser\NodeVisitorAbstract {

  var $root;

  function __construct($root, $logger) {
    $this->root = $root;
    $this->logger = $logger;
  }

  /**
   * Return an array of statistics.
   */
  function compile() {
    $result = array();

    if (file_exists($this->root . "/composer.json")) {
      $json = json_decode(file_get_contents($this->root . "/composer.json"), true /* assoc */);
      if ($json) {
        if (isset($json['require'])) {
          $result['require'] = $json['require'];
        }
        if (isset($json['requireDev'])) {
          $result['requireDev'] = $json['requireDev'];
        }
        if (isset($json['repositories'])) {
          $result['repositories'] = $json['repositories'];
        }
      }
    }

    if (file_exists($this->root . "/composer.lock")) {
      $json = json_decode(file_get_contents($this->root . "/composer.lock"), true /* assoc */);
      if ($json) {
        $result['lock_hash'] = $json['hash'];
        $packages = array();
        foreach ($json['packages'] as $package) {
          $value = array();
          $value['version'] = $package['version'];
          if (isset($package['source']['url'])) {
            $value['source'] = $package['source']['url'];
          }
          if (isset($package['source']['reference'])) {
            $value['source_hash'] = $package['source']['reference'];
          }
          if (isset($package['license'])) {
            $value['license'] = $package['license'];
          }
          if (isset($package['homepage'])) {
            $value['homepage'] = $package['homepage'];
          }

          $packages[$package['name']] = $value;
        }
        $result['lock_packages'] = $packages;
      }
    }

    return $result;
  }

}
