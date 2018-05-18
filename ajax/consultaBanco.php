<?php
    header("Content-type: text/html");
    
    require_once '../classes/banco.php';
    
    // Continuando a Sessão
    session_start();
    
    if (isset($_GET['req'])){
        switch ($_GET['req']){
            /* Consultar as vagas de administrativos de cada campus */
            case 'vaga':
               if (!isset($_GET['config']) && !isset($_GET['cam_id']) && !isset($_GET['tipo'])){
                   $resp_array = array("erro" => true, "mensagem" => "Código de vaga inválido ou inexistente!");
                   die(json_encode($resp_array));
               }    
               // Para servidores administrativos em configurações normais e com ambos
               if ($_SESSION['cargo'] > '700000' && $_GET['config'] != 'docente' && $_SESSION['post']['siape'] != '')
                   $querySQL = 'SELECT ocup_id, ocup_nome FROM ocupacao ' .
                               'WHERE ocup_id = ' . $_SESSION['cargo'];
               // Para o resto dos casos, quando não há criação de nova permuta
               else if ($_GET['tipo'] == 'permuta' || $_GET['tipo'] == 'edital')
                   $querySQL = 'SELECT ocup_id, ocup_nome FROM ocupacao ' .
                               'JOIN vaga ON vaga_tipo = ocup_id ' .
                               'WHERE ocup_id ' . ($_GET['config'] == 'docente' ? '<' : '>') . ' 700000' .
                               ($_GET['config'] == 'docente' ? ' AND ocup_id <> "499.999" AND ocup_id <> 000' : ' AND ocup_id <> 707001') .
                               ' AND vaga_campus = ' . $_GET['cam_id'] .
                               ' AND vaga_' . $_GET['tipo'] .' > 0' .
                               ' ORDER BY ocup_nome';
               // Para o resto dos casos, quando há criação de nova permuta
               else
                   $querySQL = 'SELECT ocup_id, ocup_nome FROM ocupacao ' .
                               'WHERE ocup_id ' . ($_GET['config'] == 'docente' ? '<' : '>') . ' 700000' .
                               ($_GET['config'] == 'docente' ? ' AND ocup_id <> "499.999" AND ocup_id <> 000' : ' AND ocup_id <> 707001') .
                               ' ORDER BY ocup_nome';

               $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');
               $res = $bd->executarSQL($querySQL);

               if (!is_array($res)){
                   $resp_array = array("erro" => true, "mensagem" => "Impossível extrair dados do banco!");
                   die(json_encode($resp_array));
               } else {
                   die(json_encode($res));
               }
               break;
               // Consultar a quantidade de vagas de edital ou permuta
            case 'quantidade':
               if (!isset($_GET['ocup_id']) && !isset($_GET['cam_id'])){
                   $resp_array = array("mensagem" => "Código de vaga inválido ou inexistente!");
                   die(json_encode($resp_array));
               }
               $querySQL = '(SELECT CONCAT("Candidato à(s) ", vaga_edital, " vaga(s) de Edital")AS qtde FROM vaga' .
                              ' WHERE vaga_tipo = "' . $_GET['ocup_id'] . '"' .
                              ' AND vaga_campus = ' . $_GET['cam_id'] .
                              ' AND vaga_edital > 0'.
                              ' AND vaga_permuta = 0)' .
                              ' UNION ' .
                              '(SELECT CONCAT("Candidato à(s) ", vaga_edital, " vaga(s) de Edital e com possibilidade de Permuta")AS qtde FROM vaga' .
                              ' JOIN configuracoes' .
                              ' WHERE vaga_tipo = "' . $_GET['ocup_id'] . '"' .
                              ' AND vaga_campus = ' . $_GET['cam_id'] .
                              ' AND vaga_edital > 0'.
                              ' AND vaga_permuta > 0' .
                              ' AND config_lista = "ambos")' .
                              ' UNION ' .
                              '(SELECT CONCAT("Candidato à(s) ", vaga_edital, " vaga(s) de Edital")AS qtde FROM vaga' .
                              ' JOIN configuracoes' .
                              ' WHERE vaga_tipo = "' . $_GET['ocup_id'] . '"' .
                              ' AND vaga_campus = ' . $_GET['cam_id'] .
                              ' AND vaga_edital > 0'.
                              ' AND vaga_permuta > 0' .
                              ' AND config_lista = "edital")' .
                              ' UNION ' .
                              '(SELECT "Candidato à Permuta entre vagas" AS qtde)';

               $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');
               $res = $bd->executarSQL($querySQL);

               if (!is_array($res)){
                   $resp_array = array("mensagem" => "Impossível extrair dados do banco!");
                   die(json_encode($resp_array));
               } else {
                   die(json_encode($res));
               }
               break;
        }
    } 
?>