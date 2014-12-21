<?php

namespace Statgit;

class Runner {

  function __construct($options, Logger $logger) {
    $this->options = $options;
    $this->logger = $logger;
  }

  function updateGit() {
    $this->logger->log("Pulling latest from remote...");
    passthru("cd " . escapeshellarg($this->options["root"]) . " && git pull origin master");
  }

  function exportLog() {
    // TODO

  }

  function iterateOverEachCommit() {
    // TODO

  }

  function compileStats() {
    // TODO

  }

  function generateHTML() {
    // TODO
  }


}
