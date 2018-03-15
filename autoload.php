<?php

namespace root;

error_reporting(E_ALL);

//require_once ROOT_DIR . 'config/constants.php';
require_once __DIR__ . '/config/constants.php';

require_once ROOT_DIR . 'src/csvParser.php' ;
require_once ROOT_DIR . 'src/dbConnect.php' ;
require_once ROOT_DIR . 'src/validator.php' ;