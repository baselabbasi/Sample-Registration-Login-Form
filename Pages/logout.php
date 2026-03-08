<?php
require_once '/var/www/html/Sample-Registration-Login-Form/domain/helper.php';
session_destroy();
redirect('Login.php');