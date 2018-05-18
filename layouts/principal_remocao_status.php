<?php 
    // Continuando a sessão
    if (!isset($_SESSION['post'])){
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
    // Comprovante de Inscrição
    else if(isset($_GET['comprovante'])){
        $_SESSION["alterar"] = $_POST;
        $_SESSION['modulo'] = "principal_remocao_resumo";
        header("Location: /remocao/");
        die();
    }
    // Sobre o Sistema
    else if(isset($_GET['sobre'])){
        $_SESSION["alterar"] = $_POST;
        $_SESSION['modulo'] = "principal_remocao_sobre";
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
    
    // Formatar data e hora
    function formata_data_extenso($strDate)
    {
        // Array com os dia da semana em português;
        $arrDaysOfWeek = array('Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado');
        // Array com os meses do ano em português;
        $arrMonthsOfYear = array(1 => 'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
        // Descobre o dia da semana
        $intDayOfWeek = date('w',strtotime($strDate));
        // Descobre o dia do mês
        $intDayOfMonth = date('d',strtotime($strDate));
        // Descobre o mês
        $intMonthOfYear = date('n',strtotime($strDate));
        // Descobre o ano
        $intYear = date('Y',strtotime($strDate));
        //Retorna também a hora (Adicionado por Rafael (ebalaio.com)
        $intHour = substr($strDate,10,20);
        // Formato a ser retornado
        return $arrDaysOfWeek[$intDayOfWeek] . ', ' . $intDayOfMonth . ' de ' . $arrMonthsOfYear[$intMonthOfYear] . ' de ' . $intYear . ' às '.$intHour;
    }
?>
<script type="text/javascript" >   
    function alerta(){
        alertify.alert("Atenção: Inscrições encerradas. Acompanhe o andamento da remoção na aba de Status da Remoção.");
    }
</script>
<ul id="menu_wrap" class="l_Green">
    <li class="button"><a href="?lista">Listagem Geral de Vagas</a></li>
    <!-- Impedir que o servidor faça alterações na inscrição quando o processo seletivo foi encerrado ou quando houver a troca do modo de inscrição-->
    <?php 
        $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');
        $stringSQL = "SELECT ins_nome, ins_cargo, ins_tipo, st_nome, ins_atualizacao, (SELECT COUNT(*) FROM inscricao WHERE ins_vaga_dest =  (SELECT ins_vaga_dest FROM inscricao WHERE ins_cpf = '" . $_SESSION['post']['cpf'] . "')) AS qtde
                        FROM inscricao 
                        JOIN status ON st_id = ins_status
                        WHERE ins_cpf = '" . $_SESSION['post']['cpf'] . "'";
        $res_ins = $bd->executarSQL($stringSQL);
        if ($_SESSION["post"]["config"] == "encerrado" 
            || ($_SESSION["post"]["config"] == "administrativo" && $res_ins[0]['ins_cargo'] == '707001')
            || ($_SESSION["post"]["config"] == "docente" && $res_ins[0]['ins_cargo'] != '707001')){ 
    ?>
        <li class="button"><a href="#" onclick="alerta();">Alterar dados da Inscrição</a></li>
    <?php }else{ ?>
        <li class="button"><a href="?alterar">Alterar dados da Inscrição</a></li>
    <?php } ?>
    <li class="button"><a href="?comprovante">Comprovante de Inscrição</a></li>
    <li class="button"><a href="#">Status da Remoção</a></li>
    <li class="button"><a href="?sobre">Estatísticas Gerais</a></li>
    <li class="button"><a href="?inicio">Sair do Sistema</a></li> 
</ul>
<div id="div_body" >
    <div id="div_resumo" >
        <fieldset id="fds_status" >
            <legend>Status da Remoção:</legend>
            <div class="div_campo">
                Nome:
            </div>
            <div class="div_valor">
                <?php echo $res_ins[0]['ins_nome']; ?>
            </div> 
            <div class="div_campo">
                Tipo de concorrência atual:
            </div>
            <div class="div_valor">
                <?php 
                    if ($res_ins[0]['ins_tipo'] == 'Edital'){
                        echo "Candidato à vaga de ";
                        echo $res_ins[0]['ins_tipo']; 
                    }else{
                        echo "Candidato à ";
                        echo $res_ins[0]['ins_tipo']; 
                    }
                ?>
            </div> 
            <div class="div_campo">
                Relação Candidato/Vaga:
            </div>
            <div class="div_valor">
                <?php 
                    echo ($res_ins[0]['qtde'] - 1); 
                    echo " candidato(s) na concorrência geral"
                ?>
            </div>
            <div class="div_campo">
                Status:
            </div>
            <div class="div_valor">
                <?php echo $res_ins[0]['st_nome']; ?>
            </div> 
            <div class="div_campo">
                Prazo para conclusão do processo:
            </div>
            <div class="div_valor">
                Condicionado à finalização do trâmite processual
            </div> 
            <div class="div_campo">
                Atualizado em:
            </div>
            <div class="div_valor">
                <?php echo formata_data_extenso($res_ins[0]['ins_atualizacao']); ?>
            </div> 
        </fieldset>
        <div class="div_emissao" >
            <?php
                echo "Comprovante emitido em: ";
                echo date("d/m/y"); // exibe a data no formato DD/MM/YY
                echo " às ";
                echo date("H:i"); //exibe a hora no formato HH:MM
            ?>
        </div> 
    </div> 
</div>