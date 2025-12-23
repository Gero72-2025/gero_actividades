<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?></title>
    <!-- Bootstrap CSS - Versión Local para funcionar sin internet -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/lib/bootstrap/bootstrap.min.css">
    <!-- Bootstrap Icons - Versión Local -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/lib/bootstrap-icons/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/style.css">
</head>
<body>
    <div class="app-wrapper"> 
        
        <?php 
            // Usamos la constante APPROOT para la ruta absoluta
            require_once APPROOT . '/views/layouts/navbar.php'; 
        ?>
        
        <main class="content-scrollable">
            <div class="container"> 