<?php
    // Só aceita se vier com $_POST do formulário da ficha de inscrição
    if (!isset($_POST["final"])){
        header("Location: /remocao/");
        die();
    }
    // Cancelar alterações e voltar para o comprovante sem validar nada
    else if(isset($_GET['comprovante'])){
        $_SESSION["modulo"] = "principal_remocao_resumo";
        header("Location: /remocao/");
        die();
    }    
    // Continuando sessão
    session_start();

    /* Se o javascript estiver desativado ou não funcional, garante que o select 
     * das vagas de docente seja preenchido
     * 
     * Considerar somente para docentes
     */
    if (!$_POST["vaga"] && $_SESSION["post"]["config"] == "docente"){
        if (!$_POST["destino"]){
            $_SESSION["erro"] = 1;
            $_SESSION["invalidos"] = array('destino' => array('erro' => true));
            $_SESSION["post"] = $_POST;
            header("Location: /remocao/");
            die();
        }
        else
        {
            // Atribui 0 a $_SESSION["erro"] para informar que estava 
            // apenas retornando os dados para preencher o select das 
            // vagas de docente
            $_SESSION["erro"] = 0;
            $_SESSION["post"] = $_POST;
            header("Location: /remocao/");
            die();
        }
    }   
    
    // Passando por tudo, vamos em frente com o cadastro do servidor
    require_once 'classes/cadastro.php';
    require_once 'classes/banco.php';
    require_once 'classes/utilidades.php';
    require_once 'classes/PHPMailer-master/PHPMailerAutoload.php';
    
    $remocao = new Remocao($_POST);
    
    // Se tudo estiver OK
    $resp = $remocao->checarCampos();
    
    if (is_array($resp)){
        // Houveram erros, volta com os dados no formulário
        $_SESSION["erro"] = 1;
        $_SESSION["invalidos"] = $resp;
        $_SESSION["post"] = $_POST;
        header("Location: /remocao/");
        die();  
        
    // Continuar com a inserção
    }else{
        $bd = new banco("mysql:host=localhost;dbname=remocao", "cadonline", "123456");
        
        // Insere no banco
        $bd->executarSQL($remocao->getSQLStringRemocao());

        // Preparar a mensagem
        $mensagem = "<img src='cid:ifsplogo' />";
        $mensagem .= "<h3>Prezado Servidor,</h3>\n";
        if ($_SESSION["post"]["config"] == "docente")
            $mensagem .= "<p>Informamos que foi enviada com sucesso a vossa Ficha de Inscrição para o " . $_SESSION["edital_doc"] . ", referente às Remoções a Pedido para servidores Docentes.</p>\n";
        else if ($_SESSION["post"]["config"] == "administrativo")
            $mensagem .= "<p>Informamos que foi enviada com sucesso a vossa Ficha de Inscrição para o " . $_SESSION["edital_adm"] . ", referente às Remoções a Pedido para servidores Técnicos-Administrativos.</p>\n";
        else
            $mensagem .= "<p>Informamos que foi enviada com sucesso a vossa Ficha de Inscrição referente às Remoções a Pedido para servidores.</p>\n";
        $mensagem .= "<p>Seguem os dados enviados:</p>\n";
        $mensagem .= $remocao->getHTMLTable();
        $mensagem .= "<p>Para acompanhar o andamento de seu processo, alterar o seu cadastro ou imprimir as duas vias de seu comprovante de inscrição, realize um novo acesso no sistema, clicando no link abaixo:</p>\n";
        $mensagem .= "<p>.</p>\n";
        $mensagem .= '<a href="' . $_SESSION["url_sis"] . '" target="_blank" >Sistema Integrado de Remoção Interna de Servidores</a>';
        $mensagem .= "<p>.</p>\n";
        $mensagem .= "<p>Quaisquer dúvidas entre em contato através do Portal de Concursos:</p> 
            <p><a href=\"http://concursopublico.ifsp.edu.br/contato\">Portal Concurso Público, Remoção e Redistribuição</a></p>\n";

        /* PHPMailer */
        
        try {
            $mailer = new PHPMailer(true);
            $mailer->isSMTP();                      // Mailer usará SMTP
            $mailer->Host = 'webmail.ifsp.edu.br';  // Especificando o servidor SMTP
            $mailer->SMTPAuth = true;               // Habilitar autenticação SMTP 
            $mailer->Username = 'rt000007';         // Usuário
            $mailer->Password = 'concurso';         // Senha
            $mailer->SMTPSecure = 'tls';            // Habilitar encriptação, 'ssl' também disponível
            $mailer->Priority = 'high';
            $mailer->CharSet = 'utf-8';
            $mailer->Timeout = 10;

            $mailer->From = 'automatico.concurso@ifsp.edu.br'; // Remetente:
            $mailer->FromName = 'Concurso'; // Nome:
            $mailer->addAddress($remocao->getValor("email"), $remocao->getValor("nome")); // Adicionando um destinatário
            $mailer->isHTML(true); // Email montado em HTML

            $mailer->CharSet = 'utf-8'; // Codificação de caracteres UTF-8
            $mailer->Subject = '[NÃO RESPONDER] Comprovante de Envio da Ficha de Inscrição'; // Assunto
            $mailer->Body = $mensagem;
            // A linha abaixo serve para os servidores de email sem suporte a HTML
            $mailer->AltBody = 'Para visualizar esta mensagem, use um visualizador de e-mail compatível com HTML!';
            $mailer->AddEmbeddedImage('resources/ifsplogo.png', 'ifsplogo');
            
        // Tratando erros
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); 
        } catch (Exception $e) {
            echo $e->getMessage(); 
        }
        if(!$mailer->send()) {
            echo 'Mensagem não pôde ser enviada.';
            echo 'Erro do Mailer: ' . $mailer->ErrorInfo;
        }    
        
        /* PHPMailer */

        // Redirecionar para o resumo da inscrição
        $_SESSION["final"] = $_POST;
        $_SESSION["modulo"] = "principal_remocao_resumo";
        header("Location: /remocao/");
        die();
    }
?>