<?php

class Cadastro {

    protected $postArray;

    public function __construct($_postArray) {
        $this->postArray = $_postArray;
    }

    protected function injection($_valor) {
        return addslashes($_valor);
    }

    public function setValor($_campo, $_valor) {
        if (is_string($_valor))
            $this->postArray[$_campo] = $this->injection($_valor);
        else
            $this->postArray[$_campo] = $_valor;
    }

    public function getValor($_campo) {
        return $this->postArray[$_campo];
    }
    
    protected function validaSIAPE($_siape) {
        $_siapenums = str_replace(array(" ", ".", "-"), "", $_siape);
        if (strlen($_siapenums) != 7)
            return false;
        return true;
    }

    protected function validaCPF($_cpf) {
        // Deve ter exatos 11 números, não importa se houver pontos e traço
        $_cpfnums = str_replace(array(" ", ".", "-"), "", $_cpf);
        if (strlen($_cpfnums) != 11)
            return false;
        // Evitar CPFs com números repetidos. Ex: 000.000.000-00, 111.111.111-11, etc
        $_cpfInt = (float) $_cpfnums;
        if (!$_cpfInt)
            return false;
        if ($_cpfnums == str_repeat($_cpfnums[0], 11))
            return false;

        // Cálculo do primeiro dígito
        $_sumDig1 = 0;
        for ($i = 10; $i > 1; $i--) {
            $_sumDig1 += $_cpfnums[10 - $i] * $i;
        }
        $_dig1 = ($_sumDig1 % 11 > 1) ? 11 - $_sumDig1 % 11 : 0;

        // Cálculo do segundo dígito
        $_sumDig2 = $_dig1 * 2;
        for ($i = 11; $i > 2; $i--) {
            $_sumDig2 += $_cpfnums[11 - $i] * $i;
        }
        $_dig2 = ($_sumDig2 % 11 > 1) ? 11 - $_sumDig2 % 11 : 0;

        return ($_cpfnums[9] == $_dig1 && $_cpfnums[10] == $_dig2) ? true : false;
    }
    
    protected function validaEmail($_email) {
        return (is_string(filter_var($_email, FILTER_VALIDATE_EMAIL)));
    }
}

class Remocao extends Cadastro {
    public function checarCampos($_retorna = true){
        $erros = array();

        // Campus origem
        if (!(bool)$this->postArray["origem"]) {
            if (!$_retorna)
                return false;
            $erros["origem"]["erro"] = true;
            $erros["origem"]["tipo"] = 1;
        }
        // Campus destino
        if (!(bool)$this->postArray["destino"]) {
            if (!$_retorna)
                return false;
            $erros["destino"]["erro"] = true;
            $erros["destino"]["tipo"] = 1;
        }
        // Vaga 
        if ((bool)$this->postArray["destino"]){
          if (!(bool)$this->postArray["vaga"]){
              if (!$_retorna)
                  return false;
              $erros["vaga"]["erro"] = true;
              $erros["vaga"]["tipo"] = 1;
          }
        }
        // Nome - Somente para novos servidores
        if ($_SESSION["post"]["siape"] == ""){
            if ($this->postArray["nome"] == "") {
                if (!$_retorna)
                    return false;
                $erros["email"]["erro"] = true;
                $erros["email"]["tipo"] = 1;
            }
        }
        // E-mail
        if ($this->postArray["email"] == "") {
            if (!$_retorna)
                return false;
            $erros["email"]["erro"] = true;
            $erros["email"]["tipo"] = 1;
        }else if (!$this->validaEmail($this->postArray["email"])) {
            if (!$_retorna)
                return false;
            $erros["email"]["erro"] = true;
            $erros["email"]["tipo"] = 2;
        }
        
        // Checagem OK, true ou retorna os campos
        return (count($erros)) ? $erros : true;
    }
    
    public function getHTMLTable() {
            $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');
            
            // Capturar dados do cadastro SIAPE
            $strSQL = "SELECT * FROM inscricao WHERE ins_cpf = '". $_SESSION["post"]["cpf"] . "'";
            $res_ins = $bd->executarSQL($strSQL);

            // Consultar nome de campus destino no banco de dados   
            $strSQL = "SELECT c.cam_nome, co.cam_nome AS cam_origem, oc.ocup_nome FROM campus AS c
                       JOIN campus AS co ON co.cam_id = ". $res_ins[0]['ins_origem'] .
                     " JOIN ocupacao AS oc ON oc.ocup_id = (SELECT vaga_tipo FROM vaga WHERE vaga_id = " . $res_ins[0]['ins_vaga_dest'] . ")" .
                     " WHERE c.cam_id = ". $res_ins[0]['ins_destino']; 
            
            $res = $bd->executarSQL($strSQL);
        
            // Para iterar e ajudar a montar a tabela
            $campos = array("nome" => "Nome",
                            "inscricao" => "Número de Inscrição",
                            "siape" => "Matrícula SIAPE",
                            "tel1" => "Telefone para Contato",
                            "tel2" => "Telefone Secundário",
                            "email" => "E-mail",
                            "origem" => "Campus Origem",
                            "destino" => "Campus Destino",
                            "vaga" => "Vaga a Concorrer",);

        // Princípio da tabela
        $htmlTable = <<< HTML
<table summary="Tabela de Dados Cadastrais" style="border-collapse: collapse;" >
    <thead>
        <tr>
            <th colspan="2" ><br>Dados do Cadastro<br><br></th>
        </tr>
    </thead>
    <tbody>
HTML;
        
        // foreach que itera por $campos e monta os dados básicos
        foreach ($campos as $campo => $nome){
            switch ($campo){
                case "nome":
                        $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                            $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $res_ins[0]['ins_nome'] . "</td></tr>\n";
                    break;
                case "inscricao":
                        $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                            $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $res_ins[0]['ins_inscricao'] . "</td></tr>\n";
                    break;
                case "siape":
                    // Somente para servidores cadastrados
                    if ($_SESSION["post"]["siape"] != "")
                        $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                            $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $res_ins[0]['ins_siape'] . "</td></tr>\n";
                    break;
                case "vaga":
                        $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                            $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $res[0] ['ocup_nome'] . "</td></tr>\n";
                    break;
                case "origem":
                        $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                            $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $res[0] ['cam_origem'] . "</td></tr>\n";
                    break;
                case "destino":
                        $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                            $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $res[0] ['cam_nome'] . "</td></tr>\n";
                    break;
                case "tel1":
                    if (isset($this->postArray["tel1"]) 
                                    && strcspn($this->postArray["tel1"], '0123456789') != strlen($this->postArray["tel1"])) {
                        $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                            $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $this->postArray[$campo] . "</td></tr>\n";
                    }
                    break;
                case "tel2":
                    if (isset($this->postArray["tel2"]) 
                                    && strcspn($this->postArray["tel2"], '0123456789') != strlen($this->postArray["tel2"])) {
                        $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                            $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $this->postArray[$campo] . "</td></tr>\n";
                    }
                    break;
                default:
                    $htmlTable .= "<tr><td style=\"border-bottom: 1px solid #000; \" >" . 
                        $nome . "</td><td style=\"text-align: right; border-bottom: 1px solid #000; \" >" . $this->postArray[$campo] . "</td></tr>\n";
                    break;
            }
        }
        $htmlTable .= "</tbody>\n<table>";
        return $htmlTable;
    }

    public function getSQLStringRemocao() {
        // Necessário para tradução do array em caixa alta
        require_once "classes/utilidades.php";
        $util = new utilidades();
        $traduzir = array(
            'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Ä'=>'A', 'Æ'=>'AE', 'Ç'=>'C',
            'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ð'=>'Eth',
            'Ñ'=>'N', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
            'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
   
            'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'å'=>'a', 'ä'=>'a', 'æ'=>'ae', 'ç'=>'c',
            'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'eth',
            'ñ'=>'n', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
            'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y',
           
            'ß'=>'sz', 'þ'=>'thorn', 'ÿ'=>'y',
        );
        if (isset($this->postArray["destino"])){
            // Criar conexão  
            $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456'); 
            
            // Converter data para o formato americano
            $data = substr($_SESSION["post"]["dtnasc"], 6, 4) . "-" . substr($_SESSION["post"]["dtnasc"], 3, 2) 
                    . "-" . substr($_SESSION["post"]["dtnasc"], 0, 2);  

            // Checar se o servidor já não está inscrito
            $strSQL = "SELECT ins_siape, ins_vaga_dest, ins_origem FROM inscricao WHERE ins_cpf = '". $_SESSION["post"]["cpf"] ."'";
            $ins_res = $bd->executarSQL($strSQL);
            
            // Capturar dados do cadastro SIAPE, se houverem
            $strSQL = "SELECT * FROM cadastro WHERE cad_cpf = '". $_SESSION["post"]["cpf"] ."'";
            $res = $bd->executarSQL($strSQL);
            
            /* Controle de vagas */

            // Definir Cargo para servidor não cadastrado
            if ($_SESSION["post"]["siape"] == ""){
                // Definir Cargo funcional para novos servidores docentes
                if ($_SESSION["post"]["config"] == "docente")
                    $cargo = "707001";
                // Definir Cargo funcional para novos servidores administrativos
                else if ($_SESSION["post"]["config"] == "administrativo")
                    $cargo = $this->postArray["vaga"];
                // Definir Cargo funcional para ambos os servidores
                else if ($_SESSION["post"]["config"] == "ambos"){
                    if ($this->postArray["cargo"] == "administrativo")
                        $cargo = $this->postArray["vaga"];
                    else
                        $cargo = "707001";
                }    
            }
            // Definir Cargo para servidor cadastrado
            else 
                $cargo = $res[0]['cad_cargo'];

            // Verificar se existe a vaga de origem do servidor
            $strSQL = "SELECT vaga_id, vaga_campus from vaga
                        WHERE vaga_tipo = '" . $this->postArray["vaga"] . "'" .
                        " AND vaga_cargo = " . $cargo .
                        " AND vaga_campus = " . $this->postArray["origem"]; 
            $res_vaga_orig = $bd->executarSQL($strSQL);
            
            // Vaga de origem do servidor não existe
            if (count($res_vaga_orig) == 0){
                // Criar vaga no banco
                $strSQL = "INSERT INTO `vaga` VALUES(NULL,'" . $this->postArray["vaga"] . "', '" . $cargo . "', " . $this->postArray["origem"] . ", 0, 1)";  
                $bd->executarSQL($strSQL);

                // Captura vaga_id de vaga recém criada
                $strSQL = "SELECT vaga_id, vaga_campus from vaga
                        WHERE vaga_tipo = '". $this->postArray["vaga"] . "'" .
                        " AND vaga_cargo = " . $cargo .
                        " AND vaga_campus = " . $this->postArray["origem"];
                $res_vaga_orig = $bd->executarSQL($strSQL);
                
                // Em caso de alteração de vaga
                if (isset($_SESSION['alterar'])){   
                    // Captura vaga_id de vaga de já inscrita
                    $strSQL = "SELECT ins_vaga_orig FROM inscricao WHERE ins_cpf = '". $_SESSION["post"]["cpf"] ."'";
                    $res_orig = $bd->executarSQL($strSQL);
                    // Excluir vaga de permuta anterior
                    if ($res_vaga_orig[0]['vaga_campus'] != $ins_res[0]['ins_origem']){
                        $strSQL = "UPDATE `vaga` SET vaga_permuta = (vaga_permuta - 1) WHERE vaga_id = '" . $res_orig[0]['ins_vaga_orig'] . "';";
                        $bd->executarSQL($strSQL);
                    }
                    // Verificar no banco se há vagas com permuta negativa
                    $strSQL = "SELECT vaga_id from vaga WHERE vaga_permuta < 0";
                    $res_orig = $bd->executarSQL($strSQL);
                    // Se houver alguma, alterar para zero
                    if (count($res_orig) > 0){
                        $strSQL = "UPDATE `vaga` SET vaga_permuta = 0 WHERE vaga_id = '" . $res_orig[0]['vaga_id'] . "';";  
                        $bd->executarSQL($strSQL);
                    }
                }
            }
            // Vaga de saída existe
            else if (count($res_vaga_orig) > 0){
                // Atualizar vaga no banco somente se mudar o campus de origem
                if (isset ($res_vaga_orig) && $res_vaga_orig[0]['vaga_campus'] != $ins_res[0]['ins_origem']){
                    $strSQL = "UPDATE `vaga` SET vaga_permuta = (vaga_permuta + 1) WHERE vaga_id = '" . $res_vaga_orig[0]['vaga_id'] . "';";  
                    $bd->executarSQL($strSQL);
                }
                // Em caso de alteração de vaga
                if (isset($_SESSION['alterar'])){
                    // Captura vaga_id de vaga de já inscrita
                    $strSQL = "SELECT ins_vaga_orig FROM inscricao WHERE ins_cpf = '". $_SESSION["post"]["cpf"] ."'";
                    $res_orig = $bd->executarSQL($strSQL);
                    // Excluir vaga de permuta anterior
                    if ($res_vaga_orig[0]['vaga_campus'] != $ins_res[0]['ins_origem']){
                        $strSQL = "UPDATE `vaga` SET vaga_permuta = (vaga_permuta - 1) WHERE vaga_id = '" . $res_orig[0]['ins_vaga_orig'] . "';";
                        $bd->executarSQL($strSQL);
                    }
                    // Verificar no banco se há vagas com permuta negativa
                    $strSQL = "SELECT vaga_id from vaga WHERE vaga_permuta < 0";
                    $res_orig = $bd->executarSQL($strSQL);
                    // Se houver alguma, alterar para zero
                    if (count($res_orig) > 0){
                        $strSQL = "UPDATE `vaga` SET vaga_permuta = 0 WHERE vaga_id = '" . $res_orig[0]['vaga_id'] . "';";  
                        $bd->executarSQL($strSQL);
                    } 
                }
            }
            // Verificar se existe vaga de destino do servidor
            $strSQL = "SELECT vaga_id, vaga_edital from vaga
                        WHERE vaga_tipo = '" . $this->postArray["vaga"] . "'" .
                        " AND vaga_cargo = " . $cargo .
                        " AND vaga_campus = " . $this->postArray["destino"]; 
            $res_vaga_dest = $bd->executarSQL($strSQL);
            
            // Vaga de destino do servidor não existe
            if (count($res_vaga_dest) == 0){
                // Criar vaga no banco
                $strSQL = "INSERT INTO `vaga` VALUES(NULL,'" . $this->postArray["vaga"] . "', '" . $cargo . "', " . $this->postArray["destino"] . ", 0, 0)";  
                $bd->executarSQL($strSQL);

                // Captura vaga_id de vaga recém criada
                $strSQL = "SELECT vaga_id from vaga
                        WHERE vaga_tipo = '". $this->postArray["vaga"] . "'" .
                        " AND vaga_cargo = " . $cargo .
                        " AND vaga_campus = " . $this->postArray["destino"];
                $res_vaga_dest = $bd->executarSQL($strSQL);
            }

            /* Controle de vagas */

            // Caso já exista um registro, atualizar somente os dados passados pelo formulário
            if (count($ins_res) > 0){

                // Fazer Update no registro de inscrição
                $_stringSQL  = "UPDATE `inscricao` SET ";
                // para novos servidores
                if ($_SESSION["post"]["siape"] == ""){
                    $_stringSQL .= "ins_nome = ";
                    $_stringSQL .= "'" . ereg_replace("[^A-Za-z0-9 ]", "", strtoupper($util->retirarAssentos(utf8_encode($this->postArray["nome"]), $traduzir))) . "', ";
                    // Cargo para novos servidores
                    $_stringSQL .= "ins_cargo = ";
                    $_stringSQL .= "'" . $cargo . "', ";
                    // Sexo para novos servidores
                    $_stringSQL .= "ins_sexo = ";
                    $_stringSQL .= "'" . $this->postArray["sexo"] . "', ";
                }
                // Telefone para Contato
                $_stringSQL .= "ins_tel1 = ";
                if (isset($this->postArray["tel1"]) 
                        && strcspn($this->postArray["tel1"], '0123456789') != strlen($this->postArray["tel1"]))
                    $_stringSQL .= "'" . $this->postArray["tel1"] . "', ";
                else
                    $_stringSQL .= "'', ";
                // Telefone Secundário
                $_stringSQL .= "ins_tel2 = ";
                if (isset($this->postArray["tel2"])
                        && strcspn($this->postArray["tel2"], '0123456789') != strlen($this->postArray["tel2"]))
                    $_stringSQL .= "'" . $this->postArray["tel2"] . "', ";
                else
                    $_stringSQL .= "'', ";
                // E-mail
                $_stringSQL .= "ins_email = ";
                $_stringSQL .= "'" . $this->postArray["email"] . "', ";
                // Anexo
                $_stringSQL .= "ins_anexo = ";
                $_stringSQL .= "'" . $this->postArray["anexo"] . "', ";
                // Vaga de destino
                $_stringSQL .= "ins_vaga_dest = ";
                $_stringSQL .= "" . $res_vaga_dest[0]['vaga_id'] . ", ";
                // Vaga de origem
                $_stringSQL .= "ins_vaga_orig = ";
                $_stringSQL .= "" . $res_vaga_orig[0]['vaga_id'] . ", ";
                // Campus origem
                $_stringSQL .= "ins_origem = ";
                $_stringSQL .= "" . $this->postArray["origem"] . ", ";
                // Campus destino
                $_stringSQL .= "ins_destino = ";
                $_stringSQL .= "" . $this->postArray["destino"] . ", ";
                // Data e hora da inscrição
                $_stringSQL .= "ins_data_hora = CURRENT_TIMESTAMP, ";
                // Tipo de inscrição de vaga
                if ($res_vaga_dest[0]['vaga_edital'] == 0)
                    $_stringSQL .= "ins_tipo = 'Permuta' ";
                else
                    $_stringSQL .= "ins_tipo = 'Edital' ";
                $_stringSQL .= "WHERE ins_cpf = '" . $_SESSION["post"]["cpf"] . "';";
            // Inscrever pela primeira vez
            }else{
       
                /* Inserindo os dados */
                $_stringSQL = "INSERT INTO `inscricao` \nVALUES(";
                // Indice
                $_stringSQL .= "NULL, ";
                // Número de Inscrição
                $_stringSQL .= "NULL, ";
                // SIAPE
                $_stringSQL .= "'" . $_SESSION["post"]["siape"] . "', ";
                // CPF
                $_stringSQL .= "'" . $_SESSION["post"]["cpf"] . "', ";
                // Nome para novo servidor
                if ($_SESSION["post"]["siape"] == "")
                    $_stringSQL .= "'" . ereg_replace("[^A-Za-z0-9 ]", "", strtoupper($util->retirarAssentos(utf8_encode($this->postArray["nome"]), $traduzir))) . "', ";
                // Nome para servidor cadastrado
                else
                    $_stringSQL .= "'" . $res[0]['cad_nome'] . "', ";
                // Estado Civil para novos servidores
                if ($_SESSION["post"]["siape"] == "")
                    $_stringSQL .= "'6', ";
                // Estado Civil para servidor cadastrado
                else
                    $_stringSQL .= "'" . $res[0]['cad_est_civil'] . "', ";
                // Data de nascimento
                $_stringSQL .= "'" . $data . "', ";
                // Sexo para novos servidores
                if ($_SESSION["post"]["siape"] == "")
                    $_stringSQL .= "'" . $this->postArray["sexo"] . "', ";
                // Sexo para servidor cadastrado
                else
                    $_stringSQL .= "'" . $res[0]['cad_sexo'] . "', ";
                // Telefone contato
                if (isset($this->postArray["tel1"]) 
                        && strcspn($this->postArray["tel1"], '0123456789') != strlen($this->postArray["tel1"]))
                    $_stringSQL .= "'" . $this->postArray["tel1"] . "', ";
                else
                    $_stringSQL .= "'', ";
                // Telefone secundário
                if (isset($this->postArray["tel2"])
                        && strcspn($this->postArray["tel2"], '0123456789') != strlen($this->postArray["tel2"]))
                    $_stringSQL .= "'" . $this->postArray["tel2"] . "', ";
                else
                    $_stringSQL .= "'', ";
                // E-mail
                $_stringSQL .= "'" . $this->postArray["email"] . "', ";
                // Situação funcional para novos servidores
                if ($_SESSION["post"]["siape"] == "")
                    $_stringSQL .= "'EST01', ";
                // Situação funcional para servidor cadastrado
                else
                    $_stringSQL .= "'" . $res[0]['cad_situacao'] . "', ";
                // Cargo funcional 
                $_stringSQL .= "'" . $cargo . "', ";
                // Regime para novos servidores
                if ($_SESSION["post"]["siape"] == "")
                    $_stringSQL .= "00, ";
                // Regime para servidor cadastrado
                else
                    $_stringSQL .= "" . $res[0]['cad_regime'] . ", ";
                // Anexo
                if (isset($this->postArray["anexo"]))
                    $_stringSQL .= "'" . $this->postArray["anexo"] . "', ";
                else
                    $_stringSQL .= "'', ";
                // Vaga de destino
                $_stringSQL .= "" . $res_vaga_dest[0]['vaga_id'] . ", ";
                // Vaga de origem
                $_stringSQL .= "" . $res_vaga_orig[0]['vaga_id'] . ", ";
                // Campus origem
                $_stringSQL .= "" . $this->postArray["origem"] . ", ";
                // Campus destino
                $_stringSQL .= "" . $this->postArray["destino"] . ", ";
                // Data e hora da inscrição
                $_stringSQL .= "CURRENT_TIMESTAMP, ";
                // Tipo de inscrição de vaga
                if ($res_vaga_dest[0]['vaga_edital'] == 0)
                    $_stringSQL .= "'Permuta', ";
                else
                    $_stringSQL .= "'Edital', ";
                // Status inicial do servidor
                $_stringSQL .= "00, ";
                // Atualização inicial do Status do servidor
                $_stringSQL .= "CURRENT_TIMESTAMP);\n";
                
                // Se o servidor for recém empossado, criar um número de SIAPE temporário no banco
                if ($_SESSION["post"]["siape"] == ""){
                    $_stringSQL .= "UPDATE `inscricao` SET ";
                    $_stringSQL .= "ins_siape = CONCAT('NOV', ins_indice) ";
                    $_stringSQL .= "WHERE ins_cpf = '" . $_SESSION["post"]["cpf"] . "';"; 
                }
            }
            // Para retornar o último id usando LAST_INSERT_ID do mysql
            $_stringSQL .= "SET @INDICE = (SELECT ins_indice FROM inscricao WHERE ins_cpf = '". $_SESSION["post"]["cpf"] ."');\n";
            $_stringSQL .= "UPDATE `inscricao` SET ";
            if ($_SESSION["post"]["siape"] == ""){
                $_stringSQL .= "ins_inscricao = CONCAT(CONCAT(CONCAT('NOV', ins_indice)";
                $_stringSQL .= ", LPAD(CAST(@INDICE AS CHAR), 4, '0')), '";
            }else{
                $_stringSQL .= "ins_inscricao = CONCAT(CONCAT('". $res[0]['cad_siape'] ."";
                $_stringSQL .= "', LPAD(CAST(@INDICE AS CHAR), 4, '0')), '";
            }
            $_stringSQL .= $this->postArray["origem"];
            $_stringSQL .= $this->postArray["destino"];
            $_stringSQL .= $res_vaga_dest[0]['vaga_id'];
            $_stringSQL .= "') ";
            $_stringSQL .= "WHERE ins_indice = @INDICE;\n";

            return $_stringSQL;
        } else 
            return false;
    }    
}
?>