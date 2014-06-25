<?php
require_once dirname(__FILE__) . '/../envvars.php';
require_once 'libraries/Doctrine.php';

$doctrine = new Doctrine;
$schemaTool = new \Doctrine\ORM\Tools\SchemaTool($doctrine->em);
$entityClassNames = $doctrine->em->getConfiguration()
                                 ->getMetadataDriverImpl()
                                 ->getAllClassNames();
$classMetadatas = array();
foreach ($entityClassNames as $entityName) {
	$classMetadatas[] = $doctrine->em->getClassMetadata($entityName);
}

$schemaTool = new \Doctrine\ORM\Tools\SchemaTool($doctrine->em);
$sqls = $schemaTool->getUpdateSchemaSql($classMetadatas);

if ($sqls) {
	$migrationMark = time();
	$migrationFile = fopen(dirname(__FILE__) . '/dumps/' . $migrationMark.'.php', 'w');
	fwrite($migrationFile, '<?php ' . PHP_EOL .  '$queries_' . $migrationMark . '=' . var_export($sqls, TRUE) . ';');
	fclose($migrationFile);
}