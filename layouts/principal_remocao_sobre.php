<?php 
    // Continuando a sessão
    if (!isset($_SESSION["post"])){
        $_SESSION["modulo"] = "principal_remocao_cadastro";
        header("Location: /remocao/");
        die();
    }
    // Ações do menu
    if (isset($_SESSION['alterar'])){
        // Acesso para alterar os dados da ficha de inscrição
        if(isset($_GET['alterar']) && $_SESSION["post"]["config"] != "encerrado"){
            $_SESSION["alterar"] = $_POST;
            $_SESSION["modulo"] = "principal_remocao_cadastro";
            header("Location: /remocao/?alterar");
            die();
        }
        // Acesso ao Comprovante de Inscrição
        else if(isset($_GET['comprovante'])){
            $_SESSION['modulo'] = "principal_remocao_resumo";
            header("Location: /remocao/");
            die();
        }
    }
    // Lista de vagas
    if(isset($_GET['lista'])){
        $_SESSION["modulo"] = "principal_remocao_lista";
        header("Location: /remocao/");
        die();
    }
    // Status do sistema
    if(isset($_GET['status'])){
        $_SESSION["modulo"] = "principal_remocao_status";
        header("Location: /remocao/");
        die();
    }
    /// Sair do sistema
    else if(isset($_GET['inicio'])){
        $_SESSION['modulo'] = "principal_remocao";
        header("Location: /remocao/");
        session_destroy();
        die();
    } 
    // Capturar estatísticas
    $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');
    $stringSQL = "SELECT NULL AS campus, CONCAT('total geral') AS nome, COUNT(*) AS qtde, CONCAT(  '-' ) AS config_versao
                    FROM inscricao
                    UNION 
                    SELECT c.cam_id, c.cam_nome, IFNULL(COUNT(i.ins_destino),0) , config_versao
                    FROM campus AS c
                    LEFT JOIN inscricao AS i ON c.cam_id = i.ins_destino
                    JOIN configuracoes
                    GROUP BY c.cam_id";
    $res = $bd->executarSQL($stringSQL);
    
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
    if (isset($_SESSION['alterar'])){
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
        // Capturando cargo do servidor
        $stringSQL = "SELECT ins_cargo FROM inscricao WHERE ins_cpf = '" . $_SESSION['post']['cpf'] . "'";
        $res_ins = $bd->executarSQL($stringSQL);
        if ($_SESSION["post"]["config"] == "encerrado" 
            || ($_SESSION["post"]["config"] == "administrativo" && $res_ins[0]['ins_cargo'] == '707001')
            || ($_SESSION["post"]["config"] == "docente" && $res_ins[0]['ins_cargo'] != '707001')){ 
    ?>
        <li class="button"><a href="#" onclick="alerta()">Alterar dados da Inscrição</a></li>
    <?php }else{ ?>
        <li class="button"><a href="?alterar">Alterar dados da Inscrição</a></li>
    <?php } ?>
    <li class="button"><a href="?comprovante">Comprovante de Inscrição</a></li>
    <li class="button"><a href="?status">Status da Remoção</a></li>
    <li class="button"><a href="#">Estatísticas Gerais</a></li>
    <li class="button"><a href="?inicio">Sair do Sistema</a></li> 
</ul>
<?php }else{?>
<ul id="menu_wrap" class="l_Green" text-align="right">
    <li class="button"><a href="?lista">Listagem Geral de Vagas</a></li>
    <li class="button"><a href="#">Estatísticas Gerais</a></li>
    <li class="button"><a href="?inicio">Sair do Sistema</a></li> 
</ul>
<?php } ?>
<div id="div_body" >
    <div id="div_resumo" >
        <fieldset id="fds_sobre" >
            <legend>Sobre o Sistema:</legend>
            <div class="div_campo" >
                Sigla:
            </div>
            <div class="div_valor" >
                SIRIS
            </div>
            <div class="div_campo" >
                Denominação:
            </div>
            <div class="div_valor" >
                Sistema Integrado de Remoção Interna de Servidores
            </div>
            <div class="div_campo" >
                Versão:
            </div>
            <div class="div_valor" >
                <?php echo $res[1] ['config_versao'] ?>
            </div>
            <div class="div_campo" >
                Autor:
            </div>
            <div class="div_valor" >
                Adriano Alberto Borges Ramos
            </div>
        </fieldset> 
    </div>
    <div id="div_resumo" >
        <fieldset id="fds_estatisticas" >
            <legend>Total de Inscritos:</legend>
            <?php
                for ($i=1; $i<count($res); $i++){
                    echo "<div class='div_campo' >";
                    echo $res[$i] ['nome'];
                    echo "</div>";
                    echo "<div class='div_valor' >";
                    echo $res[$i] ['qtde'];
                    echo "</div>";
                }
            ?>
            <div class="div_campo" >
                Total Geral de Inscritos:
            </div>
            <div class="div_valor" >
                <?php echo $res[0] ['qtde']; ?>
            </div>
        </fieldset> 
    </div>
    <div id="div_resumo" >
        <fieldset id="fds_historico" >
            <legend>Histórico de Acessos do Servidor:</legend>
            <?php
                $stringSQL = "SELECT ins_data_hora FROM log_acesso WHERE ins_cpf = '" . $_SESSION['post']['cpf'] . "' 
                              ORDER BY ins_data_hora ASC;";
                $timestamp = $bd->executarSQL($stringSQL);
                for ($i=0; $i<count($timestamp); $i++){
                    echo "<div class='div_campo' >";
                    echo ($i+1) . "º Acesso:";
                    echo "</div>";
                    echo "<div class='div_valor' >";
                    echo formata_data_extenso($timestamp[$i]['ins_data_hora']);
                    echo "</div>";
                }
            ?>
        </fieldset>
        <div class="div_emissao" >
                <?php
                    echo "Histórico emitido em: ";
                    echo date("d/m/y"); // exibe a data no formato DD/MM/YY
                    echo " às ";
                    echo date("H:i"); //exibe a hora no formato HH:MM
                ?>
        </div> 
    </div> 
</div>