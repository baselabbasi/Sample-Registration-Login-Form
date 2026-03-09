<?php
require_once __DIR__ . '/../domain/helper.php';
session_destroy();
redirect('Login.php');