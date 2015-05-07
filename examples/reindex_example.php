<?php

// includes
require_once('config.php');
require_once('../cps_api.php');

try {
  // creating a CPS_Connection instance
  $cpsConnection = new cps\CPS_Connection($config['connection'], $config['database'], $config['username'], $config['password'],
    'document', '//document/id', array('account' => $config['account']));

  // Reindexing storage
  $reindexRequest = new cps\CPS_Request('reindex');
  $resp = $cpsConnection->sendRequest($reindexRequest);
} catch (cps\CPS_Exception $e) {
  var_dump($e->errors());
  exit;
}
?>

