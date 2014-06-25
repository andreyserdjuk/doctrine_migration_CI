<?php

require_once dirname(__FILE__) . '/../envvars.php';
require_once dirname(__FILE__) . '/../libraries/Doctrine.php';
$doctrine = new Doctrine;

// get migrations files list
$migrationsFiles = scandir(dirname(__FILE__) . '/dumps', SCANDIR_SORT_DESCENDING);

// get current db version
$dbVersion = $doctrine->em->getRepository('models\LocalOffice')->findOneByKey('db_version');

// not found version record? - create it as last dump
if (!$dbVersion) {
	$dbVersion = new models\LocalOffice;
	$dbVersion->setKey('db_version');
	$dbVersion->setType('string');

	// take last dump and set as last version
	foreach ($migrationsFiles as $fileName) {
		if (preg_match('/^\d+\.php$/', $fileName)) {
			$dbVersion->setValue(substr($fileName, 0, -4));
			break;
		}
	}
	$doctrine->em->persist($dbVersion);
    $doctrine->em->flush();
    // I think, initially we have one migration file, remove directory dots:
    $migrationsFiles = array_slice($migrationsFiles, 0, 1);
} else {
	// get migrations order for apply
	array_splice($migrationsFiles, array_search($dbVersion->getValue() . '.php', $migrationsFiles));
}

$conn = $doctrine->em->getConnection();
if (!empty($migrationsFiles)) {
	for ($i=count($migrationsFiles); $i >= 0; $i--) { 
		try {
			if (isset($migrationsFiles[$i])) {
				$migrationFile = $migrationsFiles[$i];
				require_once dirname(__FILE__) . '/dumps/' . $migrationFile;
				$migrationMark = substr($migrationFile, 0, -4);
				$queriesVariable = 'queries_' . $migrationMark;
				foreach ($$queriesVariable as $q) {
					$conn->executeQuery('SET FOREIGN_KEY_CHECKS=0');
					$conn->executeQuery($q);
				}
				$dbVersion->setValue($migrationMark);
				$doctrine->em->persist($dbVersion);
			    $doctrine->em->flush();
			}
		} catch(Exception $e) {
	        $date = new DateTime("now");
	        $file = fopen(APPPATH . 'logs' . DIRECTORY_SEPARATOR . 'log_migration_' . $date->format("Y-m-d") . '.log', 'a+');
	        fwrite($file, $date->format("Y-m-d H:i:s") . " | Error in file $migrationFile : " . $e->getMessage() . PHP_EOL);
	        fclose($file);   
		}
	}
}