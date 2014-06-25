<?php

require_once dirname(__FILE__) . '/envvars.php';
require_once 'libraries/Doctrine.php';

$doctrine = new Doctrine;

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($doctrine->em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($doctrine->em)
));

\Doctrine\ORM\Tools\Console\ConsoleRunner::run($helperSet);