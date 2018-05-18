<?php
    /* 
     * Este script valida os seguintes dados:
     * - Se o servidor é Técnico-Administrativo, Docente ou se as inscrições estão encerradas;
     * - Se o servidor digitou corretamente os dados com base em uma consulta 
     * no número do SIAPE;
     * - Se o servidor não possui cadastro no SIAPE.
     * 
     * Outras funções:
     * - Se o servidor tiver digitado corretamente o número da Matrícula SIAPE,
     * o script vai validar se o CPF e a data de nascimento estão corretos;
    */
    
    /* Digite no banco configuracoes, na coluna config_inscricao para alterar o status do concurso para:
     * "docente" - Para restringir o processo seletivo somente à docentes
     * "administrativo" - Para restringir o processo seletivo somente à técnicos-administrativos
     * "ambos" - Para permitir que ambos os servidores docentes e administrativos possam se inscrever
     * "encerrado" - Para impedir que todos se inscrevam, mas permite que servidores inscritos acessem para consulta do status
     * "manutenção" - indica status de manutenção do sistema. Permite testes apenas com alguns servidores seletos no sistema e indica horário de retorno do sistema no ar
     */
        
    // Só aceita se vier com $_POST do formulário
    if (!isset($_POST['envio'])){
        header("Location: /remocao/");
        die();
    }    
    // Segurança
    session_start();
    // Limpar variáveis de sessão caso já tenha existido uma sessão anterior a esta
    unset($_SESSION['cargo']);
    unset($_SESSION['alterar']);
    unset($_SESSION['url_sis']);
    unset($_SESSION['url_adm']);
    unset($_SESSION['url_doc']);
    unset($_SESSION['edital_adm']);
    unset($_SESSION['edital_doc']);
    
    /* proteger contra session fixation. Gerar novo
    identificador ao logar no sistema. */
    session_regenerate_id();

    /* Checagem do reCaptcha */
    require_once "classes/recaptchalib.php";
    // Chave para 10.2.6.24, trocar quando for para o servidor
    $chavePrivada = "6Le7yPcSAAAAAD5SBSvI_2BJhDw_O5pdG2WKqlzJ";
    $resposta = recaptcha_check_answer($chavePrivada, $_SERVER["REMOTE_ADDR"], 
            $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
    
    if (!$resposta->is_valid){
            $_SESSION["erro"] = 1;
            $_SESSION["invalidos"] = array('reCaptcha' => array('erro' => true));
            $_SESSION["post"] = $_POST;
            $_SESSION["captchaErro"] = $resposta->error;
            header("Location: /remocao/");
            die();
    }
    /* Checagem do reCaptcha */
    
    // Passando por tudo, vamos em frente com o cadastro do candidato
    require_once 'classes/cadastro.php';
    require_once 'classes/banco.php';
    require_once 'classes/utilidades.php';

    // Criar conexão  
    $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');
    
    // Checar antes se o servidor já não está inscrito
    if (isset($_POST["siape"]) && $_POST["siape"] != "")
        // Se o servidor tiver digitado o SIAPE
        $strSQL = "SELECT ins_siape FROM inscricao WHERE ins_siape = '". $_POST["siape"] ."'
                UNION
                SELECT config_inscricao FROM configuracoes";
    else
        // Se o servidor estiver cadastrado sem número de SIAPE, consultar pelo CPF
        $strSQL = "SELECT ins_siape FROM inscricao WHERE ins_cpf = '". $_POST["cpf"] ."'
                UNION
                SELECT config_inscricao FROM configuracoes";
    $res = $bd->executarSQL($strSQL);
    
    // Converter data para o formato americano
    $dados = substr($_POST["dtnasc"], 6, 4) . "-" . substr($_POST["dtnasc"], 3, 2) . "-" . substr($_POST["dtnasc"], 0, 2);
    
    // Se o servidor já estiver inscrito
    if (is_numeric($res[0]['ins_siape']) || substr($res[0]['ins_siape'], 0, -4) == "NOV"){
        
        // Definir o tipo de inscrição do concurso com base no banco de dados
        $_POST["config"] = $res[1]['ins_siape'];
        
        // Barrar se o servidor tentar acessar o comprovante de inscrição
        // se ele informar que não possui SIAPE, mas na realidade já possui cadastro no sistema
        if ($_POST["siape"] == "" && substr($res[0]['ins_siape'], 0, -4) != "NOV") {
            $_SESSION["erro"] = 1;
            $_SESSION["invalidos"] = array("siape" => array("erro" => true, "tipo" => 1) );
            $_SESSION["post"] = $_POST;
            header("Location: /remocao/");
            die();
        }   
        // Consultar CPF e data de nascimento na inscrição
        $strSQL = "SELECT ins_cpf,ins_dtnasc FROM inscricao WHERE ins_cpf = '". $_POST["cpf"] ."'";
        $res = $bd->executarSQL($strSQL);
        
        // Candidato errou o CPF
        if($res[0]['ins_cpf'] != $_POST["cpf"]){
            $_SESSION["erro"] = 1;
            $_SESSION["invalidos"] = array("cpf" => array("erro" => true, "tipo" => 3) );
            $_SESSION["post"] = $_POST;
            header("Location: /remocao/");
            die();
            
        // Candidato errou a data de nascimento
        }else if($res[0]['ins_dtnasc'] != $dados){
            $_SESSION["erro"] = 1;
            $_SESSION["invalidos"] = array("dtnasc" => array("erro" => true, "tipo" => 2) );
            $_SESSION["post"] = $_POST;
            header("Location: /remocao/");
            die();
        }
        // Registrar acesso no log
        $stringSQL  = "INSERT INTO `log_acesso` \nVALUES(NULL, '" . $_POST["cpf"] . "', CURRENT_TIMESTAMP);\n";
        $bd->executarSQL($stringSQL);
        
        // Iniciar a sessão direto no comprovante de inscrição
        $_SESSION["post"] = $_POST;
        // Se o SIAPE for de um servidor teste, mudar o config para o modo "ambos"
        if (($_POST["siape"] == "9999999" || $_POST["siape"] == "8888888") && $_SESSION["post"]["config"] == 'manutenção')
            $_SESSION["post"]["config"] = "ambos";
        // Mostrar o status da inscrição do servidor
        $_SESSION["modulo"] = "principal_remocao_status";
        header("Location: /remocao/");
        die();
    } 
    else
        // Definir o tipo de inscrição do concurso com base no banco de dados
        $_POST["config"] = $res[0]['ins_siape'];
        
    // Checar se o servidor já está no banco cadastro
    if (isset($_POST["siape"]) && $_POST["siape"] != ""){
        // Se o servidor já tiver SIAPE
        $strSQL = "SELECT cad_siape, cad_cpf, cad_nasc, cad_cargo FROM cadastro WHERE cad_siape = '". $_POST["siape"] ."'";
        $res = $bd->executarSQL($strSQL);
        
        // Se o servidor estiver no cadastro quando preencher todos os campus
        if (count($res) == 0){
            $_SESSION["erro"] = 1;
            $_SESSION["invalidos"] = array("siape" => array("erro" => true, "tipo" => 2) );
            $_SESSION["post"] = $_POST;
            header("Location: /remocao/");
            die();
        }
    }else{
        // Se o servidor estiver cadastrado sem número de SIAPE, consultar pelo CPF
        $strSQL = "SELECT cad_cpf, cad_nasc, cad_cargo FROM cadastro WHERE cad_cpf = '". $_POST["cpf"] ."'";
        $res = $bd->executarSQL($strSQL);
        
        // Se o servidor afirmar que ainda não possui matrícula SIAPE mas um registro 
        // no cadastro foi localizado, avisar o servidor e impedir o cadastro
        if (count($res) > 0){
            $_SESSION["erro"] = 1;
            $_SESSION["invalidos"] = array("siape" => array("erro" => true, "tipo" => 5) );
            $_SESSION["post"] = $_POST;
            header("Location: /remocao/");
            die();
        }
    }
    // Se o SIAPE for de um servidor teste, mudar o config para o modo "ambos"
    if (isset($_POST["siape"]) && ($_POST["siape"] == "9999999" || $_POST["siape"] == "8888888") && isset($_SESSION["post"]["config"]) && $_SESSION["post"]["config"] == 'manutenção')
        $_POST["config"] = "ambos";
    
    // O processo seletivo está encerrado ou em manutenção
    if($_POST["config"] == "encerrado" || $_POST["config"] == "manutenção"){
        $_SESSION["erro"] = 1;
        if ($_POST["config"] == "encerrado")
            $_SESSION["invalidos"] = array("encerrado" => array("erro" => true));
        else
            $_SESSION["invalidos"] = array("manutenção" => array("erro" => true));
        $_SESSION["post"] = null;
        header("Location: /remocao/");
        die();
    }
    
    // Candidato digitou todos os dados corretamente
    if($res[0]['cad_cpf'] == $_POST["cpf"] && $res[0]['cad_nasc'] == $dados){        
        switch ($_POST["config"]){
            // O processo seletivo destina-se à docentes
            case "docente":
                if($res[0]['cad_cargo'] == "707001") 
                {
                    // Registrar acesso no log
                    $stringSQL = "INSERT INTO `log_acesso` \nVALUES(NULL, '" . $_POST["cpf"] . "', CURRENT_TIMESTAMP);\n";
                    $bd->executarSQL($stringSQL);

                    // Passar os dados para a próxima sessão de cadastro
                    $_SESSION["post"] = $_POST;
                    $_SESSION["modulo"] = "principal_remocao_lista";
                    header("Location: /remocao/");
                    die();
                    
                // Candidato não é docente
                }else{
                    $_SESSION["erro"] = 1;
                    $_SESSION["invalidos"] = array("siape" => array("erro" => true, "tipo" => 3) );
                    $_SESSION["post"] = $_POST;
                    header("Location: /remocao/");
                    die();
                }
                break;
            // O processo seletivo destina-se à servidores técnicos-administrativos
            case "administrativo":
                if($res[0]['cad_cargo'] != "707001") 
                {
                    // Registrar acesso no log
                    $stringSQL  = "INSERT INTO `log_acesso` \nVALUES(NULL, '" . $_POST["cpf"] . "', CURRENT_TIMESTAMP);\n";
                    $bd->executarSQL($stringSQL);

                    // Passar os dados para a próxima sessão de cadastro
                    $_SESSION["post"] = $_POST;
                    $_SESSION["modulo"] = "principal_remocao_lista";
                    header("Location: /remocao/");
                    die();
                
                // Candidato não é técnico-administrativo
                }else{
                    $_SESSION["erro"] = 1;
                    $_SESSION["invalidos"] = array("siape" => array("erro" => true, "tipo" => 4) );
                    $_SESSION["post"] = $_POST;
                    header("Location: /remocao/");
                    die();
                }
                break;
            // O processo seletivo permite inscrição de ambos os servidores técnicos-administrativos quanto docentes
            case "ambos":
                
                // Registrar acesso no log
                $stringSQL  = "INSERT INTO `log_acesso` \nVALUES(NULL, '" . $_POST["cpf"] . "', CURRENT_TIMESTAMP);\n";
                $bd->executarSQL($stringSQL);

                // Passar os dados para a próxima sessão de cadastro
                $_SESSION["post"] = $_POST;
                
                // Precisamos informar ao sistema se o servidor é administrativo ou docente
                $res[0]['cad_cargo'] == "707001" ? $_SESSION["post"]["config"] = "docente" : $_SESSION["post"]["config"] = "administrativo";
                
                $_SESSION["modulo"] = "principal_remocao_lista";
                header("Location: /remocao/");
                die();
                break;
        }
    // Candidato errou o CPF   
    }else if($res[0]['cad_cpf'] != $_POST["cpf"] && $_POST["siape"] != ""){
        $_SESSION["erro"] = 1;
        $_SESSION["invalidos"] = array("cpf" => array("erro" => true, "tipo" => 3) );
        $_SESSION["post"] = $_POST;
        header("Location: /remocao/");
        die();
        
    // Candidato errou a data de nascimento
    }else if($res[0]['cad_nasc'] != $_POST["dtnasc"] && $_POST["siape"] != ""){
        $_SESSION["erro"] = 1;
        $_SESSION["invalidos"] = array("dtnasc" => array("erro" => true, "tipo" => 2) );
        $_SESSION["post"] = $_POST;
        header("Location: /remocao/");
        die();
        
    // Candidato é um servidor recém empossado que ainda não possui número de Matrícula do SIAPE
    }else{
        // Registrar acesso no log
        $stringSQL = "INSERT INTO `log_acesso` \nVALUES(NULL, '" . $_POST["cpf"] . "', CURRENT_TIMESTAMP);\n";
        $bd->executarSQL($stringSQL);

        // Passar os dados para a próxima sessão de cadastro
        $_SESSION["post"] = $_POST;
        $_SESSION["modulo"] = "principal_remocao_lista";
        header("Location: /remocao/");
        die();
    }
?>