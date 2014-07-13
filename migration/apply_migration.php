<?php

require_once dirname(__FILE__) . '/../envvars.php';
require_once dirname(__FILE__) . '/../libraries/Doctrine.php';
$doctrine = new \Doctrine;

// get migrations files list
$migrFiles = scandir(dirname(__FILE__) . '/dumps', SCANDIR_SORT_DESCENDING);

if (!empty($migrFiles)) {
	// remove directory dots
	$migrFiles = array_slice($migrFiles, 0, -2);

	// get current db_version
	$dbVersion = $doctrine->em->getRepository('models\LocalOffice')->findOneByKey('db_version');
	$dbRunning = $doctrine->em->getRepository('models\LocalOffice')->findOneByKey('updater_running');

	if (!$dbRunning) {
		$dbRunning = new \models\LocalOffice;
		$dbRunning->setKey('updater_running');
		$dbRunning->setType('integer');
		$doctrine->em->persist($dbRunning);
	}

	// all applications should know, that schema update is running
	$dbRunning->setValue('1');
	$doctrine->em->flush();

	if (!$dbVersion) {
		// not found db_version? - create it as last dump
		$dbVersion = new \models\LocalOffice;
		$dbVersion->setKey('db_version');
		$dbVersion->setType('string');
		$doctrine->em->persist($dbVersion);
	} else {
		// get migrations order to apply
		array_splice($migrFiles, array_search($dbVersion->getValue() . '.php', $migrFiles));
	}

	$conn = $doctrine->em->getConnection();
	sort($migrFiles, SORT_NUMERIC);

	foreach ($migrFiles as $f) {
		try {
			require_once dirname(__FILE__) . '/dumps/' . $f;
			$migrationMark = substr($f, 0, -4);
			$queriesVariable = 'queries_' . $migrationMark;
			foreach ($$queriesVariable as $q) {
				$conn->executeQuery('SET FOREIGN_KEY_CHECKS=0');
				$conn->executeQuery($q);
			}
			$dbVersion->setValue($migrationMark);
		    $doctrine->em->flush();
		} catch(\Exception $e) {
	        $date = new \DateTime("now");
	        $file = fopen(APPPATH . 'logs/log_migration_' . $date->format("Y-m-d") . '.log', 'a+');
	        fwrite($file, $date->format("Y-m-d H:i:s") . ' | Error in file $migrationFile : ' . $e->getMessage() . PHP_EOL);
	        fclose($file);   
		}
	}
	
	$dbRunning->setValue('0');
	$doctrine->em->flush();
}
