<?php 
    /* Digite no banco configuracoes, na coluna config_lista para alterar o status do concurso para:
     * "edital" - Para restringir o processo seletivo somente à vagas de edital
     * "permuta" - Para restringir o processo seletivo somente à vagas de permuta
     * "ambos" - Para permitir que ambas as vagas de edital e permuta sejam acessadas pelos servidores
     */

    // Continuando a sessão
    if (!isset($_SESSION['post'])){
        header("Location: /remocao/");
        die();
    }

    // Conexão com o banco de dados (sem ser feita usando o script já existente)
    $con = mysql_connect("localhost", "cadonline", "123456");
    // Checar se conexão foi feita com sucesso
    if (!$con){
       die('\nNão foi possível se conectar ao banco de dados: ' . mysql_error());
    }
    // Selecionar banco de dados
    mysql_select_db("remocao", $con);
    // Arrumar acentos ortográficos
    mysql_query("SET NAMES 'utf8'", $con);
    // Selecionar vagas de edital no banco
    $sql_edital = 'SELECT IF(ocup_id <700000, CONCAT("Docente (", ocup_nome,")"),ocup_nome) AS Ocupação, 
                        cam_nome AS Campus, vaga_edital AS Vagas, cam_id, ocup_id, 
                        config_lista, config_edital_adm, config_data_adm, config_url_adm,
                        config_edital_doc, config_data_doc, config_url_doc
                        FROM vaga
                        JOIN campus ON cam_id = vaga_campus
                        JOIN ocupacao ON ocup_id = vaga_tipo
                        JOIN configuracoes
                        WHERE vaga_edital >0
                        ORDER BY Ocupação';
    // Selecionar vagas de permuta no banco
    $sql_permuta = 'SELECT IF(ocup_id < 700000,CONCAT("Docente (",ocup_nome,")"),ocup_nome) AS Ocupação, 
                        cam_nome AS Campus, vaga_permuta AS Vagas, cam_id, ocup_id, 
                        config_lista
                        FROM vaga
                        JOIN campus ON cam_id = vaga_campus
                        JOIN ocupacao ON ocup_id = vaga_tipo
                        JOIN configuracoes
                        WHERE vaga_permuta > 0
                        ORDER BY Ocupação';
    $res_edital = mysql_query($sql_edital,$con);
    $res_permuta = mysql_query($sql_permuta,$con);
    // Arrays que irão conter o resultado do query
    $dados_edital = array();
    $dados_permuta = array();
    // Fetch
    while($linha = mysql_fetch_assoc($res_edital))
       $dados_edital[] = $linha;
    while($linha = mysql_fetch_assoc($res_permuta))
       $dados_permuta[] = $linha;
    $nomesColuna_permuta = array_keys(reset($dados_permuta));
    $nomesColuna_edital = array_keys(reset($dados_edital));
    // Encerrar conexão
    mysql_close($con);
    
    // Prosseguir para o formulário de inscrição
    if(isset($_POST["cadastro"])){
        $_SESSION['modulo'] = "principal_remocao_cadastro";
        header("Location: /remocao/");
        die();
    }
    // Prosseguir para o fromulário com campus pré-carregado
    else if(isset($_GET['cam_id']) && isset($_GET['ocup_id']) && isset($_GET['tipo'])){
        // Criar conexão  
        $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');
        // Capturar cargo do banco de dados
        $stringSQL = "SELECT cad_cargo FROM cadastro WHERE cad_cpf = '" . $_SESSION['post']['cpf'] . "'";
        $res = $bd->executarSQL($stringSQL);
        // Casos passíveis de restrição na seleção da lista para impedir que o usuário do sistema acesse páginas não
        // permitidas no momento do primeiro acesso
        if ((isset($res[0]['cad_cargo']) && $res[0]['cad_cargo'] == 707001 && count($res) > 0 && $_GET['ocup_id'] > 700000) 
            || (isset($res[0]['cad_cargo']) && $res[0]['cad_cargo'] != 707001 && count($res) > 0 && $res[0]['cad_cargo'] != $_GET['ocup_id'])
            || ($_SESSION["post"]["config"] == "administrativo" && $_GET['ocup_id'] < 700000)
            || ($_SESSION["post"]["config"] == "docente" && $_GET['ocup_id'] > 700000)
            || ($_GET['tipo'] == 'edital' && $dados_edital[1]['config_lista'] == 'permuta')
            || ($_GET['tipo'] == 'permuta' && $dados_edital[1]['config_lista'] == 'edital')){
            // Mostrar mensagem de erro alertando que a vaga clicada não pode ser selecionada
        ?>
            <script type="text/javascript" >   
                alertify.alert("Atenção: Cargo divergente da ocupação do servidor ou do processo seletivo atual. Selecione novamente.");
            </script>
        <?php 
        // Para os casos restantes, passar para o formulário de inscrição
        }else{
            $_SESSION['modulo'] = "principal_remocao_cadastro";
            header("Location: /remocao/?cam_id=".$_GET['cam_id']."&ocup_id=".$_GET['ocup_id']."&tipo=".$_GET['tipo']);
            die();  
        }
    }
    // Ações do menu
    if (isset($_SESSION['alterar'])){
        // Alterar os dados da ficha de inscrição
        if(isset($_GET['alterar']) && $_SESSION["post"]["config"] != "encerrado"){
            $_SESSION["alterar"] = $_POST;
            $_SESSION["modulo"] = "principal_remocao_cadastro";
            header("Location: /remocao/?alterar");
            die();
        }
        // Comprovante de Inscrição
        else if(isset($_GET['comprovante'])){
            $_SESSION['modulo'] = "principal_remocao_resumo";
            header("Location: /remocao/");
            die();
        }
        // Status do Sistema
        else if(isset($_GET['status'])){
            $_SESSION['modulo'] = "principal_remocao_status";
            header("Location: /remocao/");
            die();
        }
    }
    // Sobre o Sistema
    if(isset($_GET['sobre'])){
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
    if (isset($_SESSION['alterar'])){
?>
<script type="text/javascript" >   
    function alerta(){
        alertify.alert("Atenção: Inscrições encerradas. Acompanhe o andamento da remoção na aba de Status da Remoção.");
    }
</script>
<ul id="menu_wrap" class="l_Green">
    <li class="button"><a href="#">Listagem Geral de Vagas</a></li>
    <!-- Impedir que o servidor faça alterações na inscrição quando o processo seletivo foi encerrado ou quando houver a troca do modo de inscrição-->
    <?php 
        $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');
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
    <li class="button"><a href="?sobre">Estatísticas Gerais</a></li>
    <li class="button"><a href="?inicio">Sair do Sistema</a></li> 
</ul>
<?php }else{?>
<ul id="menu_wrap" class="l_Green">
    <li class="button"><a href="#">Listagem Geral de Vagas</a></li>
    <li class="button"><a href="?sobre">Estatísticas Gerais</a></li>
    <li class="button"><a href="?inicio">Sair do Sistema</a></li> 
</ul>
<?php } ?>
<div id="div_body">
    <div id="div_resumo" >
        <fieldset id="fds_status" >
            <legend>Editais em Andamento:</legend>
            <div class="div_campo" style="display: <?=($_SESSION["post"]["config"] == "docente" || $_SESSION["post"]["config"] == "ambos")?'block':'none';?>;">
                Docente:
            </div>
            <div class="div_valor" style="display: <?=($_SESSION["post"]["config"] == "docente" || $_SESSION["post"]["config"] == "ambos")?'block':'none';?>;">
                <a href=<?php echo $dados_edital[0]['config_url_adm']; ?>>
                    <?php echo $dados_edital[0]['config_edital_adm']; ?>
                </a>
                <?php
                    echo " - ";
                    echo $dados_edital[0]['config_data_adm'];
                ?>
            </div>
            <div class="div_campo" style="display: <?=($_SESSION["post"]["config"] == "administrativo" || $_SESSION["post"]["config"] == "ambos")?'block':'none';?>;">
                Administrativo:
            </div>
            <div class="div_valor" style="display: <?=($_SESSION["post"]["config"] == "administrativo" || $_SESSION["post"]["config"] == "ambos")?'block':'none';?>;">
                <a href=<?php echo $dados_edital[0]['config_url_doc']; ?>>
                    <?php echo $dados_edital[0]['config_edital_doc']; ?>
                </a>
                <?php
                    echo " - ";
                    echo $dados_edital[0]['config_data_doc'];
                ?>
            </div>
        </fieldset>
    </div> 
    <div id="div_formulario" class="formulario">
        <div id="div_edital" style="display: <?=($dados_edital[0]['config_lista'] != 'permuta')?'block':'none';?>;">
            <p id="p_edital" >Vagas de Edital</p>
            <div class="Listagem" >
                <table>
                    <tr>
                        <?php
                           // Imprimir coluna para inscrição
                           if (!isset($_SESSION['alterar']))
                              echo "<td>#</td>";
                           // Buscar títulos das colunas
                           foreach($nomesColuna_edital as $nomeColuna){
                              if ($nomeColuna != "cam_id" && $nomeColuna != "ocup_id" && $nomeColuna != "config_lista"
                                      && $nomeColuna != "config_edital_adm" && $nomeColuna != "config_data_adm" 
                                      && $nomeColuna != "config_url_adm" && $nomeColuna != "config_edital_doc"
                                      && $nomeColuna != "config_data_doc" && $nomeColuna != "config_url_doc")
                                 echo "<td>$nomeColuna</td>";
                           }
                        ?>
                    </tr>
                        <?php
                           // Imprimir as linhas
                           foreach($dados_edital as $linha){

                              echo "<tr>";
                              if (!isset($_SESSION['alterar']))
                                 echo "<td><a href='?cam_id=".$linha["cam_id"]
                                      ."&ocup_id=".$linha['ocup_id']."&tipo=edital'>Inscrição</a></td>";

                              foreach($nomesColuna_edital as $nomeColuna){
                                 if ($nomeColuna != "cam_id" && $nomeColuna != "ocup_id" && $nomeColuna != "config_lista"
                                        && $nomeColuna != "config_edital_adm" && $nomeColuna != "config_data_adm" 
                                        && $nomeColuna != "config_url_adm" && $nomeColuna != "config_edital_doc"
                                        && $nomeColuna != "config_data_doc" && $nomeColuna != "config_url_doc")
                                    echo "<td>".$linha[$nomeColuna]."</td>";
                              }
                              echo "</tr>";
                           }
                        ?>
                </table>
            </div>
        </div>
        <?php if (mysql_num_rows($res_permuta) > 0){ ?>
            <div id="div_permuta" style="display: <?=($dados_permuta[0]['config_lista'] != 'edital')?'block':'none';?>;">
                <p id="p_permuta" >Intenções de Permutas</p>
                <div class="Listagem" >
                    <table >
                        <tr>
                            <?php
                               // Imprimir coluna para inscrição
                               if (!isset($_SESSION['alterar']))
                                  echo "<td>#</td>";
                               // Buscar títulos das colunas
                               foreach($nomesColuna_permuta as $nomeColuna){
                                  if ($nomeColuna != "cam_id" && $nomeColuna != "ocup_id" && $nomeColuna != "config_lista")
                                     echo "<td>$nomeColuna</td>";
                               }
                            ?>
                        </tr>
                            <?php
                               // Imprimir as linhas
                               foreach($dados_permuta as $linha){
                                  echo "<tr>";
                                  if (!isset($_SESSION['alterar']))
                                     echo "<td><a href='?cam_id=".$linha["cam_id"]
                                          ."&ocup_id=".$linha["ocup_id"]."&tipo=permuta'>Inscrição</a></td>";

                                  foreach($nomesColuna_permuta as $nomeColuna){
                                     if ($nomeColuna != "cam_id" && $nomeColuna != "ocup_id" && $nomeColuna != "config_lista")
                                        echo "<td>".$linha[$nomeColuna]."</td>";
                                  }
                                  echo "</tr>";
                               }
                            ?>
                    </table>
                </div>
            </div>
        <?php } if (!isset($_SESSION['alterar'])){ ?>
            <div id="div_inscrever" style="display: <?=($dados_permuta[0]['config_lista'] != 'edital')?'block':'none';?>;">
                <form name ="frm_inscrever" method ="post" name=action="/remocao/">
                    <input type="hidden" value="1" name="cadastro"/>
                    <input type = "submit" name = "Criar Permuta" value = "Criar Permuta" title = "Criar Permuta"/>
                </form>
            </div>
        <?php } ?>
    </div>
</div>