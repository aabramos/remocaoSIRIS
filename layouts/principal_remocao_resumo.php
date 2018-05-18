<?php 
    // Continuando a sessão
    if (!isset($_SESSION["post"])){
        $_SESSION["modulo"] = "principal_remocao_cadastro";
        header("Location: /remocao/");
        die();
    }
    // Ações do menu
    // Lista de vagas
    if(isset($_GET['lista'])){
        $_SESSION["alterar"] = $_POST;
        $_SESSION["modulo"] = "principal_remocao_lista";
        header("Location: /remocao/");
        die();
    }
    // Alterar os dados da ficha de inscrição
    else if(isset($_GET['alterar']) && $_SESSION["post"]["config"] != "encerrado"){
        $_SESSION["alterar"] = $_POST;
        $_SESSION["modulo"] = "principal_remocao_cadastro";
        header("Location: /remocao/?alterar");
        die();
    }
    // Sobre o Sistema
    else if(isset($_GET['sobre'])){
        $_SESSION["alterar"] = $_POST;
        $_SESSION['modulo'] = "principal_remocao_sobre";
        header("Location: /remocao/");
        die();
    }  
    // Status do Sistema
    else if(isset($_GET['status'])){
        $_SESSION["alterar"] = $_POST;
        $_SESSION['modulo'] = "principal_remocao_status";
        header("Location: /remocao/");
        die();
    }
    // Sair do sistema
    else if(isset($_GET['inicio'])){
        $_SESSION['modulo'] = "principal_remocao";
        header("Location: /remocao/");
        session_destroy();
        die();
    }
    // Criar conexão  
    $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');

    // Capturar dados da inscrição
    $strSQL = "SELECT * FROM inscricao WHERE ins_cpf = '". $_SESSION["post"]["cpf"] . "'";
    $res_ins = $bd->executarSQL($strSQL);

    // Definir descrição de sexo
    $res_ins[0]['ins_sexo'] == "M" ? $res_ins[0]['ins_sexo'] = "Masculino" : $res_ins[0]['ins_sexo'] = "Feminino"; 

    // Consultar tabelas
    $strSQL = "SELECT est_nome, reg_nome, sit_nome, ocup_vaga.ocup_nome AS ocupacao_vaga, ocup_cargo.ocup_nome AS ocupacao_cargo, cam_dest.cam_nome AS cam_destino, 
        cam_orig.cam_nome AS cam_origem
                   FROM est_civil 
                   JOIN reg_trabalho ON reg_id = '". $res_ins[0]['ins_regime'] .
                "' JOIN situacao ON sit_id = '". $res_ins[0]['ins_situacao'] .
                "' JOIN ocupacao AS ocup_cargo ON ocup_cargo.ocup_id = '". $res_ins[0]['ins_cargo'] .
                "' JOIN ocupacao AS ocup_vaga ON ocup_vaga.ocup_id = (SELECT vaga_tipo FROM vaga WHERE vaga_id = " . $res_ins[0]['ins_vaga_dest'] . ")" .
                " JOIN campus AS cam_dest ON cam_dest.cam_id = '". $res_ins[0]['ins_destino'] .
                "' JOIN campus AS cam_orig ON cam_orig.cam_id = '". $res_ins[0]['ins_origem'] .
                "' WHERE est_id = '". $res_ins[0]['ins_est_civil'] . "'";

    $res = $bd->executarSQL($strSQL);
?>
<script type="text/javascript" >   
    function imprimir(){
        window.print();
    }    
    function alerta(){
        alertify.alert("Atenção: Inscrições encerradas.\nAcompanhe o andamento da remoção na aba de Status da Remoção.");
    }
</script>
<ul id="menu_wrap" class="l_Green">
    <li class="button"><a href="?lista">Listagem Geral de Vagas</a></li>
    <!-- Impedir que o servidor faça alterações na inscrição quando o processo seletivo foi encerrado ou quando houver a troca do modo de inscrição-->
    <?php 
        if ($_SESSION["post"]["config"] == "encerrado" 
            || ($_SESSION["post"]["config"] == "administrativo" && $res_ins[0]['ins_cargo'] == '707001')
            || ($_SESSION["post"]["config"] == "docente" && $res_ins[0]['ins_cargo'] != '707001')){ 
    ?>
        <li class="button"><a href="#" onclick="alerta()">Alterar dados da Inscrição</a></li>
    <?php }else{ ?>
        <li class="button"><a href="?alterar">Alterar dados da Inscrição</a></li>
    <?php } ?>
    <li class="button"><a href="#">Comprovante de Inscrição</a></li>
    <li class="button"><a href="?status">Status da Remoção</a></li>
    <li class="button"><a href="?sobre">Estatísticas Gerais</a></li>
    <li class="button"><a href="?inicio">Sair do Sistema</a></li> 
</ul>
<div id="div_body" >
    <div id="div_menu" >
        <?php if (isset($_SESSION["final"])){ ?>
        <p style="font-weight: bold;">Ficha de inscrição enviada com sucesso!</p><br/>
        <p>Uma confirmação será enviada para o endereço de e-mail cadastrado.</p>
        <p>Se você não receber a confirmação, verifique a caixa de spam de seu e-mail.</p><br/>
        <?php } ?>
        <p>Para imprimir as duas vias de seu Comprovante de Envio de Inscrição, clique no ícone abaixo:</p>
        <a href ="#" id="a_impressao" onclick="imprimir()" title="Imprimir Comprovante" >
            <img alt="Imprimir Comprovante" src="resources/IFSP_printer.png">
        </a>
    </div>
    <div id="div_titulo" >
            <p id="p_titulo" >Comprovante de Envio de Inscrição</p>
            <p id="p_numero" > Número de Inscrição: <?php echo $res_ins[0]['ins_inscricao'];?></p> 
    </div>
    <div id="div_resumo" >
        <fieldset id="fds_pessoal" >
            <legend>Dados Pessoais:</legend>
                <?php if (is_numeric($res_ins[0]['ins_siape'])){ ?>
            <div class="div_campo">
                Matrícula SIAPE:
            </div>
            <div class="div_valor">
                <?php echo $res_ins[0]['ins_siape']; ?>
            </div>
                <?php } ?>
            <div class="div_campo" >
                Nome:
            </div>
            <div class="div_valor" >
                <?php echo $res_ins[0]['ins_nome']; ?>
            </div>
            <div class="div_campo" >
                CPF:
            </div>
            <div class="div_valor" >
                <?php echo $_SESSION["post"]["cpf"]; ?>
            </div>
                <?php if (is_numeric($res_ins[0]['ins_siape'])){ ?>
            <div class="div_campo" >
                Estado Civil:
            </div>
            <div class="div_valor" >
                <?php echo $res[0] ['est_nome']; ?>
            </div>
                <?php } ?>
            <div class="div_campo" >
                Data de Nascimento:
            </div>
            <div class="div_valor" >
                <?php echo $_SESSION["post"]["dtnasc"]; ?>
            </div>
            <div class="div_campo" >
                Sexo:
            </div>
            <div class="div_valor" >
                <?php echo $res_ins[0]['ins_sexo']; ?>
            </div>
        </fieldset>
        <fieldset id="fds_contato" >
            <legend>Contato:</legend>
                <?php if ($res_ins[0]['ins_tel1'] != "" 
                        && strcspn($res_ins[0]['ins_tel1'], '0123456789') != strlen($res_ins[0]['ins_tel1'])) { ?>
            <div class="div_campo" >
                Telefone para Contato:
            </div>
            <div class="div_valor" >
                <?php echo $res_ins[0]['ins_tel1']; ?>
            </div>
                <?php } if ($res_ins[0]['ins_tel2'] != "" 
                        && strcspn($res_ins[0]['ins_tel2'], '0123456789') != strlen($res_ins[0]['ins_tel2'])) { ?>
            <div class="div_campo" >
                Telefone Secundário:
            </div>
            <div class="div_valor" >
                <?php echo $res_ins[0]['ins_tel2']; ?>
            </div>
                <?php } ?>
            <div class="div_campo" >
                E-Mail:
            </div>
            <div class="div_valor" >
                <?php echo $res_ins[0]['ins_email']; ?>
            </div>
        </fieldset>
        <fieldset id="fds_documento" >
            <legend>Dados Funcionais:</legend>
            <div class="div_campo" >
                Situação Funcional:
            </div>
            <div class="div_valor" >
                <?php echo $res[0] ['sit_nome']; ?>
            </div>
                <!-- Mostrar esse campo somente se as inscrições forem para docentes -->
                <?php if ($_SESSION["post"]["config"] == "docente"){ ?>
            <div class="div_campo" >
                Cargo:
            </div>
            <div class="div_valor" >
                <?php echo $res[0] ['ocupacao_cargo']; ?>
            </div>
                <?php } if (is_numeric($res_ins[0]['ins_siape'])){ ?>
            <div class="div_campo" >
                Regime:
            </div>
            <div class="div_valor" >
                <?php echo $res[0] ['reg_nome']; ?>
            </div>
                <?php } ?>
        </fieldset>
        <fieldset id="fds_inscricao" >
            <legend>Inscrição:</legend>
            <div class="div_campo" >
                Campus origem:
            </div>
            <div class="div_valor" >
                <?php echo $res[0] ['cam_origem']; ?>
            </div>
            <div class="div_campo" >
                Campus no qual deseja remover-se:
            </div>
            <div class="div_valor" >
                <?php echo $res[0] ['cam_destino']; ?>
            </div>
            <div class="div_campo" >
                Vaga a Concorrer:
            </div>
            <div class="div_valor" >
                <?php  
                    echo $res_ins[0]['ins_vaga_dest'];
                    echo " - ";
                    echo $res[0] ['ocupacao_vaga']; 
                ?>
            </div>
                <!-- Se o campo de anexo for preenchido  -->
                <?php if ($res_ins[0]['ins_anexo'] != ""){ ?>
            <div class="div_campo" >
                Relação de anexos no processo:
            </div>
            <div class="div_anexo" >
                <?php echo wordwrap(nl2br($res_ins[0]['ins_anexo']), 62, "<br />", true); ?>
            </div>
                <?php } ?>
            <div class="div_emissao" >
                <?php
                    echo "<br /><br />Inscrição realizada em: ";
                    // exibe a data no formato DD/MM/YY
                    echo substr($res_ins[0]['ins_data_hora'], 8, 2) . "/" 
                            . substr($res_ins[0]['ins_data_hora'], 5, 2) . "/"    
                            . substr($res_ins[0]['ins_data_hora'], 2, 2); 
                    echo " às ";
                    //exibe a hora no formato HH:MM
                    echo substr($res_ins[0]['ins_data_hora'], 11, 5);
                ?>
            </div>
            <div class="div_emissao" >
                <?php
                    echo "Comprovante emitido em: ";
                    echo date("d/m/y"); // exibe a data no formato DD/MM/YY
                    echo " às ";
                    echo date("H:i"); //exibe a hora no formato HH:MM
                ?>
            </div> 
        </fieldset>
    </div>
        <!-- Mostrar esse campo somente se as inscrições forem para docentes -->
        <?php if ($_SESSION["post"]["config"] == "docente"){ ?>
    <div id="div_anexos_adicionais">
        <p> Documentação adicional anexa ao processo (se houver): </p>
    </div>
        <?php } ?>
</div>