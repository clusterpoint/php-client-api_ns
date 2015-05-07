<?php

// includes
require_once('config.php');
require_once('../cps_simple.php');

try {
  // creating a CPS_Connection instance
  $cpsConnection = new cps\CPS_Connection($config['connection'], $config['database'], $config['username'], $config['password'],
    'document', '//document/id', array('account' => $config['account']));

// creating a CPS_Simple instance
  $cpsSimple = new cps\CPS_Simple($cpsConnection);

  // looking up one document - listing only the name
  $document = $cpsSimple->lookupSingle('id1', array('document' => 'no', 'name' => 'yes'));
  echo $document->name . '<br />';

  // looking up multiple documents - listing only the name
  $documents = $cpsSimple->lookupMultiple(array('id2', 'id3'), array('document' => 'no', 'name' => 'yes'));
  foreach ($documents as $id => $document) {
    echo $document->name . '<br />';
  }
} catch (cps\CPS_Exception $e) {
  var_dump($e->errors());
  exit;
}
?>