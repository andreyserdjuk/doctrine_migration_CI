Doctrine 2.* migration tool is used, when Doctrine is integrated 
in CodeIgniter. It's assumed, that Doctrine is installed by composer 
and placed in libraries directory and db has table 'local_office'.
Soon I'll break these dependencies.


create_migration.php creates sql dump, which is necessary to apply to 
current database. It is good to check dump, because it can be 
partially wrong.


apply_migration.php applies dumps in direct chronological order,
beginning with current dump (excluding it) - it's db_version value
in local_office table.


Initially db_version is created during the first launch of
apply_migration.php, and its value is set to last applied dump.
