<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <!-- orrigir os erros provocados pelo IE9, IE10 -->
        <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">
        <?php if (!isset($limpo)) { ?>
        <script type="text/javascript" src="script/mootools-core-1.4.5-full-compat.js"></script>
        <script type="text/javascript" src="script/iMask-full.js"></script>
        <script type="text/javascript" src="script/iMask-init.js"></script>
        <script src="script/alertify.min.js"></script>
        <?php } ?>
        <link rel="shortcut icon" href="resources/siris.ico" type="image/x-icon"/>
        <link rel="stylesheet" type="text/css" href="css/public.css"/>
        <link rel="stylesheet" href="css/alertify.core.css" />
        <link rel="stylesheet" href="css/alertify.bootstrap.css" />
        <!-- Área específica da página requisitante -->
        <?php if (isset($estilos)) foreach ($estilos as $estilo) { ?>
            <link rel="stylesheet" type="text/css" href="<?php echo 'css/' . $estilo . '.css'; ?>"/>
        <?php } ?>
        <?php if (isset($scripts)) foreach ($scripts as $script) { ?>
            <script type="text/javascript" src="<?= 'script/' . $script . '.js'; ?>" ></script>
        <?php } ?>
        <title>SIRIS - <?= $titulo; ?></title>
        <!-- Área específica da página requisitante -->
    </head>
    <body <?= isset($incluirBody) ? $incluirBody : ''; ?> >
        <noscript>
        <div style='border: 1px solid #F7941D; background: #FEEFDA; text-align: center; clear: both; height: 80px; position: relative;'>
            <div style='width: 800px; margin: 0 auto; text-align: left; padding: 0; overflow: hidden; color: black;'>
                <div style='width: 800px; float: left; font-family: Arial, sans-serif;'>
                    <div style='font-size: 14px; font-weight: bold; margin-top: 12px; text-align: center;'>
                        O seu Navegador encontra-se desatualizado ou com Javascript desativado.
                    </div>
                    <div style='font-size: 12px; margin-top: 6px; line-height: 12px; text-align: center;'>
                        Este formulário utiliza algumas funções de código Javascript, sem elas seu funcionamento é
                        instável.<br/>
                        Para navegar melhor neste site e poder concluir seu cadastro com sucesso, por favor, atualize seu navegador
                        ou ative suporte ao Javascript.
                    </div>
                </div>
            </div>
        </div>
        </noscript>
        <div id="div_pagina">
            <div id="div_header">
                <a href="?lista" title="SIRIS" id="div_header"></a>                
            </div>