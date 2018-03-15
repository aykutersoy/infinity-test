<?php

namespace root;

require_once './autoload.php';

use root\src\CsvParser;
use root\src\DbConnect;

$db = DbConnect::getInstance();
new CsvParser($db);
