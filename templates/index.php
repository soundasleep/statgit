<h1>Development statistics for <?php echo $stats['summary']['name']; ?></h1>

<dl>
  <dt>Generated</dt>
  <dd><?php echo date('r'); ?></dd>

  <dt>Latest revision</dt>
  <dd><?php echo $this->linkRevision($stats['summary']['last_hash']); ?></dd>

  <dt>Report period</dt>
  <dd><?php echo date('Y-m-d', strtotime($stats['summary']['first_commit'])); ?> to <?php echo date('Y-m-d', strtotime($stats['summary']['first_commit'])); ?></dd>

  <dt>Total files</dt>
  <dd><?php echo number_format($stats['summary']['total_files']); ?></dd>

  <dt>Total lines of code</dt>
  <dd><?php echo number_format($stats['summary']['total_loc']); ?></dd>

  <dt>Developers</dt>
  <dd><?php echo number_format($stats['summary']['author_count']); ?></dd>

</dl>
