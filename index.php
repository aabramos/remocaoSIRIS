<?php
session_start();
// Defina a seguinte variável para habilitar o módulo que desejar,
// caso não exista o módulo na sessão.

if (isset($_GET["modulo"])){
    $_SESSION["modulo"] = $_GET["modulo"];
    header("Location: /");
    die();
}

// Exemplo: Módulo Formulário de Inscrição $modulo = "inscricao";
$mod_select = "principal_remocao";
$modulo = (isset($_SESSION["modulo"]))?$_SESSION["modulo"]:$mod_select;

/* Área com os parâmetros para o cabeçalho */
// O switch foi colocado para permitir melhor personalização e facilitar a alteração
// dos módulos.
// A ordem importa, pois as últimas CSS, se houverem conflitos, serão as usadas

switch ($modulo) {    
    case 'principal_remocao':
        $estilos = array('principal_remocao', $modulo, 'datepicker');
        $scripts = array('principal_remocao', $modulo, 'datepicker', 'utilidades');
        $titulo = 'Acesso ao Sistema';
        break;
    case 'principal_remocao_cadastro':
        $estilos = array($modulo);
        $scripts = array('principal_remocao_cadastro', $modulo, 'utilidades');
        $titulo = 'Ficha de Inscrição';
        break;
    case 'principal_remocao_lista':
        $estilos = array($modulo);
        $scripts = array('utilidades');
        $titulo = 'Lista de Vagas';
        break;
    case 'principal_remocao_resumo':
        $estilos = array($modulo);
        $titulo = 'Comprovante de Inscrição';
        break;
    case 'principal_remocao_sobre':
        $estilos = array($modulo);
        $titulo = 'Sobre o Sistema';
        break;
    case 'principal_remocao_status':
        $estilos = array($modulo);
        $titulo = 'Status do Sistema';
        break;
    default: // Padrão atual é principal_remocao
        $estilos = array('principal_remocao', $modulo, 'datepicker');
        $scripts = array('principal_remocao', $modulo, 'datepicker', 'utilidades');
        $titulo = 'Acesso ao Sistema';
}

include 'layouts/header.php';
/* Área com os parâmetros para o cabeçalho */

require_once 'classes/banco.php';
require_once 'classes/utilidades.php';
require_once 'classes/recaptchalib.php';

// Carrega o módulo definido
if (strpos($modulo, "principal") > 1)
    include "layouts/principal_" . $modulo . ".php";
else
    include "layouts/" . $modulo . ".php"; 

/* Área com os parâmetros para o Rodapé */

// Mês da última extração, identificação da sessão e versão do sistema
$bd = new banco('mysql:host=localhost;dbname=remocao', 'cadonline', '123456');
if ((isset($_SESSION['post']['cpf'])) && $_SESSION['post']['cpf'] != '')
    $strSQL = "SELECT ins_data_hora, config_extracao, config_versao FROM log_acesso 
                JOIN configuracoes
                WHERE ins_cpf =  '" . $_SESSION['post']['cpf'] . "' 
                ORDER BY ins_data_hora DESC";
else
    $strSQL = "SELECT config_extracao, config_versao FROM configuracoes";
$footer = $bd->executarSQL($strSQL);

/* Área com os parâmetros para o Rodapé */
include 'layouts/footer.php';
?>