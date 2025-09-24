<?php
    session_start();
    switch($_REQUEST['mode']){
        case 'logout':
            unset($_SESSION);
            session_destroy();
            header('location: login.php');
            break;
        default :
            
    }

