<?php
use Doctrine\ORM\EntityManager,
    Doctrine\ORM\Configuration,
    Doctrine\ORM\Tools\Setup;

require 'vendor/autoload.php';

Class Doctrine {
    public function __construct() {
        
        require APPPATH . 'config'. DIRECTORY_SEPARATOR .'database.php';
        
        /**
         * Autogenerate proxy classes, if TRUE.
         * Try to find cache (Apc, Memcached, Redis), if FALSE.
         */
        $isDevMode = ENVIRONMENT == 'development';

        // setup loaders
        $entitiesClassLoader = new \Doctrine\Common\ClassLoader('models', rtrim(APPPATH, '/'));
        $entitiesClassLoader->register();

        // create configuration
        $proxiesDir = APPPATH . 'models'. DIRECTORY_SEPARATOR .'proxies';
        $metadataPaths = array(APPPATH . 'models');
        // vendor/doctrine/orm/lib/Doctrine/ORM/Tools/Setup.php
        $config = Setup::createAnnotationMetadataConfiguration($metadataPaths, $isDevMode, $proxiesDir, null, true);
        $config->setAutoGenerateProxyClasses(false);

         // SQL query logger
        if (ENVIRONMENT != 'production') {
            $logger = new DumpSQLLogger;
            $config->setSQLLogger($logger);
        }
 
        // Database connection information
        $connectionOptions = array(
            'driver' => 'pdo_mysql',
            'user' => $db['default']['username'],
            'password' => $db['default']['password'],
            'host' => $db['default']['hostname'],
            'dbname' => $db['default']['database'],
            'charset' => $db['default']['char_set'],
            'driverOptions' => array(1002=>'SET NAMES ' . $db['default']['char_set'])
        );
 
        // Create EntityManager
        $this->em = EntityManager::create($connectionOptions, $config);
    }

    public function backupEm() {
        $this->em2 = clone $this->em;
    }

    public function restoreEm() {
        $this->em = clone $this->em2;
    }
}

class DumpSQLLogger implements Doctrine\DBAL\Logging\SQLLogger
{
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $sql .= PHP_EOL;
        $params = print_r($params, true);
        $types = print_r($types, true);

        $fileName = new DateTime("now");
        $fileName = 'doctrine_log_' . $fileName->format('Y-m-d');
        $file = fopen(APPPATH . 'logs' . DIRECTORY_SEPARATOR . $fileName . '.txt', 'a+');
        $dtime = new \DateTime();
        $dtime = $dtime->format("Y-m-d H:i:s");
        fwrite($file, "$dtime | $sql $params $types\r\n");
        fclose($file);        
    }

    public function stopQuery()
    {
    }
}