<?php
    session_start();
    unset($_SESSION['carrito']);
    header('Location: alquiler.php');
    exit();
?>