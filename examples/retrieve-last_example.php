<?php

// includes
require_once('config.php');
require_once('../cps_api.php');

try {
  // creating a CPS_Connection instance
  $cpsConnection = new cps\CPS_Connection($config['connection'], $config['database'], $config['username'], $config['password'],
    'document', '//document/id', array('account' => $config['account']));

  // retrieve 10 last documents
  $listLastRequest = new cps\CPS_RetrieveLastRequest(0, 10);
  $listLastResponse = $cpsConnection->sendRequest($listLastRequest);
  foreach ($listLastResponse->getDocuments() as $id => $document) {
    echo $document->name . '<br />';
  }
} catch (cps\CPS_Exception $e) {
  var_dump($e->errors());
  exit;
}
?>