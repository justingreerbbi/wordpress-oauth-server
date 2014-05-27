<?php
// Logout and redirect
session_start();
session_destroy();

header('Location: /');