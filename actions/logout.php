<?php
require_once '../lib/helpers.php';
session_destroy();
redirect('login.php');