<?php

define('CREATE_SQLITE_OSTN02_ON_FIRST_RUN',0);
define('USE_SQLITE_OSTN02_IF_AVAIL',1); //HIGH DISK CONSUMPTION, low memory use, quick initialisation, moderate speed
define('LOAD_OSTN02_INTO_MEM',0); //Low disk use, slow initialisation, HIGH MEMORY CONSUMPTION, very fast speed
//Fall back is to directly access OSTN02 bz archive: low disk/memory use, quick initialisation, VERY SLOW SPEED
define('USE_OSTN02_TABLE_CACHE',1);
define('MAX_OSTN02_CACHE',1000); //Prevent the cache from gobbling memory


?>
