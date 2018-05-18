<?php 
    // Continuando a sessão
    if (!isset($_SESSION['post'])){
        header("Location: /remocao/");
        die();
    }

    // Criar conexão  
    $bd = new banco('mysql:host=localhost;dbname=remocao','cadonline','123456');

    // Capturar dados do cadastro SIAPE, se o servidor existir
    if (isset($_SESSION['post']['siape'])){
        $strSQL = "SELECT * FROM cadastro WHERE cad_cpf = '". $_SESSION['post']['cpf'] . "'";
        $res_cad = $bd->executarSQL($strSQL);
        
        // Gravando variáveis na sessão ativa para consulta
        $_SESSION['cargo'] = $res_cad[0]['cad_cargo'];
        // Definir descrição de sexo
        $res_cad[0]['cad_sexo'] == 'M' ? $res_cad[0]['cad_sexo'] = "Masculino" : $res_cad[0]['cad_sexo'] = "Feminino";  
        
        // Convertendo $res_cad[0]['cad_habilitacao'] para o formato do banco de dados:
        switch ($res_cad[0]['cad_habilitacao']){
            case '103': $res_cad[0]['cad_habilitacao'] = '103.755'; break;
            case '755': $res_cad[0]['cad_habilitacao'] = '103.755'; break;
            case '2': $res_cad[0]['cad_habilitacao'] = '2.704'; break;
            case '704': $res_cad[0]['cad_habilitacao'] = '2.704'; break;
            case '43': $res_cad[0]['cad_habilitacao'] = '43.207'; break;
            case '207': $res_cad[0]['cad_habilitacao'] = '43.207'; break;
            case '629': $res_cad[0]['cad_habilitacao'] = '629.703'; break;
            case '703': $res_cad[0]['cad_habilitacao'] = '629.703'; break;
            case '499': $res_cad[0]['cad_habilitacao'] = '499.999'; break;
            case '999': $res_cad[0]['cad_habilitacao'] = '499.999'; break;
        }    
        // Consultar tabelas 
        $strSQL = "SELECT est_nome, reg_nome, sit_nome, ocup.ocup_nome AS ocup_nome, form.ocup_nome AS form_nome,  
                cam_id, cam_nome, config_url_sistema, config_edital_adm, config_url_adm,
                config_edital_doc, config_url_doc, config_lista
                FROM est_civil 
                JOIN reg_trabalho ON reg_id = '" . $res_cad[0]['cad_regime'] .
             "' JOIN situacao ON sit_id = '" . $res_cad[0]['cad_situacao'] .
             "' JOIN ocupacao AS ocup ON ocup.ocup_id = '" . $res_cad[0]['cad_cargo'] .
             "' JOIN ocupacao AS form ON form.ocup_id = '" . $res_cad[0]['cad_habilitacao'] .
             "' JOIN campus 
                JOIN configuracoes 
                WHERE est_id = '". $res_cad[0]['cad_est_civil'] . 
             "' ORDER BY cam_nome";
        
    // Se o servidor não existir, consultar somente os dados do select de campus
    }else if (!isset($_SESSION['post']['siape'])){
        $strSQL = "SELECT cam_id, cam_nome, config_url_sistema, config_edital_adm, 
                config_url_adm, config_edital_doc, config_url_doc, config_lista FROM campus
                JOIN configuracoes 
                ORDER BY cam_nome";
    }
    $res = $bd->executarSQL($strSQL);
    
    // Mandar o candidato de volta para a lista caso ele tente modificar o url
    if (isset($_GET['tipo']) && isset($_GET['ocup_id']) && isset($_GET['cam_id']))
        if (($_GET['tipo'] == 'edital' && $res[0]['config_lista'] == 'permuta')
            || ($_GET['tipo'] == 'permuta' && $res[0]['config_lista'] == 'edital')
            || ($_GET['tipo'] == '' && $_GET['ocup_id'] == '' && $_GET['cam_id'] == '' && $res[0]['config_lista'] == 'edital')
            || ($_GET['tipo'] == '' && $_GET['ocup_id'] == '' && $_GET['cam_id'] != '')
            || ($_GET['tipo'] != '' && $_GET['ocup_id'] == '' && $_GET['cam_id'] == '')
            || ($_GET['tipo'] == '' && $_GET['ocup_id'] != '' && $_GET['cam_id'] != '')
            || ($_GET['tipo'] != '' && $_GET['ocup_id'] == '' && $_GET['cam_id'] != '')
            || ($_GET['tipo'] != '' && $_GET['ocup_id'] != '' && $_GET['cam_id'] == '')){

            $_SESSION["modulo"] = "principal_remocao_lista";
            header("Location: /remocao/");
            die();
        }
    
    // Gravando variáveis na sessão ativa para consulta na hora de formular o e-mail
    $_SESSION['url_sis'] = $res[0]['config_url_sistema'];
    $_SESSION['url_adm'] = $res[0]['config_url_adm'];
    $_SESSION['edital_adm'] = $res[0]['config_edital_adm'];
    $_SESSION['url_doc'] = $res[0]['config_url_doc'];
    $_SESSION['edital_doc'] = $res[0]['config_edital_doc'];

    // Se o servidor já estiver inscrito, preencher automaticamente
    if (isset($_SESSION['alterar'])){
        $strSQL = "SELECT * FROM inscricao WHERE ins_cpf = '". $_SESSION['post']['cpf'] . "' ";
        $res_ins = $bd->executarSQL($strSQL);
        
        // Gravando variáveis na sessão ativa para consulta
        $_SESSION['cargo'] = $res_ins[0]['ins_cargo'];
    } 
    
    // Ações do menu
    if (isset($_SESSION['alterar'])){
        // Comprovante de Inscrição
        if(isset($_GET['comprovante'])){
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
    // Voltar para a lista de vagas
    if(isset($_GET['lista'])){
        $_SESSION["modulo"] = "principal_remocao_lista";
        header("Location: /remocao/");
        die();
    } 
    // Sobre o Sistema
    else if(isset($_GET['sobre'])){
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
<ul id="menu_wrap" class="l_Green">
    <li class="button"><a href="?lista">Listagem Geral de Vagas</a></li>
    <li class="button"><a href="#">Alterar dados da Inscrição</a></li>
    <li class="button"><a href="?comprovante">Comprovante de Inscrição</a></li>
    <li class="button"><a href="?status">Status da Remoção</a></li>
    <li class="button"><a href="?sobre">Estatísticas Gerais</a></li>
    <li class="button"><a href="?inicio">Sair do Sistema</a></li> 
</ul>
<?php }else{?>
<ul id="menu_wrap" class="l_Green">
    <li class="button"><a href="?lista">Listagem Geral de Vagas</a></li>
    <li class="button"><a href="?sobre">Estatísticas Gerais</a></li>
    <li class="button"><a href="?inicio">Sair do Sistema</a></li> 
</ul>
<?php } ?>
<div id="div_body">
    <div id="div_titulo" >
        <!-- Restringir o texto de vagas de edital ou permuta quando a lista está restrita por um dos dois -->
        <?php if ($res[0]['config_lista'] == 'edital' || ($res[0]['config_lista'] == 'ambos' && isset($_GET['tipo']) && $_GET['tipo'] == 'edital')){ ?>
            <p id="p_titulo" >Formulário de Intenção de Remoção por vaga de Edital</p>
        <?php }else if (isset($_SESSION['alterar'])){ ?>
            <p id="p_titulo" >Alteração de Intenção de Remoção</p>
        <?php }else{ ?>
            <p id="p_titulo" >Formulário de Intenção de Remoção por Permuta</p>
        <?php } ?>
    </div>
    <div id="div_formulario" class="formulario">
        <form name="frm_remocao" method="post" action="cadastro_remocao.php">
            <input type="hidden" value="1" name="final"/>
            <?php if (isset($_SESSION['erro'])) {echo '<input id="inp_erro" type="hidden" value="1">'; unset($_SESSION['erro']);} ?>
            <fieldset id="fds_pessoal" style="display: <?php if (isset($_SESSION['post']['siape'])) echo "block"; else echo "none";?>;">
                <legend>Confira os seus Dados Pessoais</legend>
                <div id="div_dados" >
                    <label id="lbl_siape" class="lbl_desc" title="Matrícula SIAPE" >Matrícula SIAPE:</label>
                    <label id="lbl_dadosiape" class="lbl_dados" title="Matrícula SIAPE" /><?php  if (isset($_SESSION['post']['siape'])) echo $_SESSION['post']['siape']; ?></label>
                </div>       
                <div id="div_dados" >
                    <label id="lbl_nome" class="lbl_desc" title="Nome" >Nome:</label>
                    <label id="lbl_dadonome" class="lbl_dados" title="Nome" /><?php  if (isset($res_cad[0]['cad_nome'])) echo $res_cad[0]['cad_nome']; ?></label>
                </div> 
                <div id="div_dados" >
                    <label id="lbl_cpf" class="lbl_desc" title="CPF" >CPF:</label>
                    <label id="lbl_dadocpf" class="lbl_dados" title="CPF" /><?php  if (isset($_SESSION['post']['cpf'])) echo $_SESSION['post']['cpf']; ?></label>
                </div>
                <div id="div_dados" >
                    <label id="lbl_sexo" class="lbl_desc" title="Sexo" >Sexo:</label>
                    <label id="lbl_dadosexo" class="lbl_dados" title="Sexo" /><?php  if (isset($res_cad[0]['cad_sexo'])) echo $res_cad[0]['cad_sexo']; ?></label>
                </div>
                <div id="div_dados" >
                    <label id="lbl_est_civil" class="lbl_desc" title="Estado Civil" >Estado Civil:</label>
                    <label id="lbl_dadocivil" class="lbl_dados" title="Estado Civil" /><?php  if (isset($res[0]['est_nome'])) echo $res[0] ['est_nome']; ?></label>
                </div>
                <div id="div_dados" >
                    <label id="lbl_dtnasc" class="lbl_desc" title="Data de Nascimento" >Data de Nascimento:</label>
                    <label id="lbl_dadonasc" class="lbl_dados" title="Data de Nascimento" /><?php  if (isset($_SESSION['post']['dtnasc'])) echo $_SESSION['post']['dtnasc']; ?></label>
                </div>  
                <div id="div_dados" >
                    <label id="lbl_sitfunc" class="lbl_desc" title="Situação Funcional" >Situação Funcional:</label>
                    <label id="lbl_dadofunc" class="lbl_dados" title="Situação Funcional" /><?php  if (isset($res[0]['sit_nome'])) echo $res[0] ['sit_nome']; ?></label>
                </div>  
                <div id="div_dados" >
                    <label id="lbl_cargo" class="lbl_desc" title="Cargo" >Cargo:</label>
                    <label id="lbl_dadocargo" class="lbl_dados" title="Cargo" /><?php  if (isset($res[0]['ocup_nome'])) echo $res[0] ['ocup_nome']; ?></label>
                </div> 
                <div id="div_dados" >
                    <label id="lbl_reg" class="lbl_desc" title="Regime de Trabalho" >Regime de Trabalho:</label>
                    <label id="lbl_dadoreg" class="lbl_dados" title="Regime de Trabalho" /><?php  if (isset($res[0]['reg_nome'])) echo $res[0] ['reg_nome']; ?></label>
                </div>
                <!-- Somente para docentes ou ambos -->
                <?php 
                    if (isset($res_cad[0]['cad_habilitacao']) && $res_cad[0]['cad_habilitacao'] != '499.999' && $res_cad[0]['cad_habilitacao'] != '000'){ 
                ?>
                <div id="div_dados" >
                    <label id="lbl_ocup" class="lbl_desc" title="Formação" >Formação:</label>
                    <label id="lbl_dadoocup" class="lbl_dados" title="Formação" /><?php if (isset($res[0]['form_nome'])) echo $res[0]['form_nome']; ?></label>
                </div>
                <?php } ?>
                </fieldset>
                <fieldset id="fds_novo" style="display: <?php if (!isset($_SESSION['post']['siape'])) echo "block"; else echo "none";?>;">
                <legend>Dados Pessoais</legend>
                <div id="div_nome" class="<?php if (isset($erros["nome"]["erro"])) echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)">
                    <label id="lbl_nome" class="lbl_requirido" for="inp_nome">Nome *</label>
                    <img id="img_erro_nome" alt="Erro!" src="resources/error.png" style="display: none;" />
                    <input id="inp_nome" value="<?php if (isset($res_ins[0]['ins_nome'])) echo $res_ins[0]['ins_nome'];?>" name="nome" type="text" size="40" maxlength="60" 
                           onfocus="input_focus(this)" onblur="input_blur(this)" title="Nome Completo" />
                </div>        
                <div id="div_cargo" class="div_campo" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)"
                     onchange="vagasDestino(destino)" style="display: <?php if (!isset($_GET['tipo'])) echo "block"; else echo "none";?>;">
                    <label id="lbl_cargo" class="lbl_requirido" >Cargo *</label>
                    <label id="lbl_cargod" for="inp_cargod" >Docente</label>
                    <input id="inp_cargod" type="radio" name="cargo" value="docente" 
                           <?php 
                                if ($_SESSION['post']['config'] == 'administrativo') 
                                    echo 'disabled';
                                else if ($_SESSION['post']['config'] == 'docente')
                                    echo 'checked="checked" disabled';
                                else if ($_GET['ocup_id'] < '700000')
                                    echo 'checked="checked"';
                                else if ($_SESSION['cargo'] == '707001' && $_SESSION['cargo'] != '')
                                    echo 'checked="checked"';
                           ?>
                           title="docente">
                    <label id="lbl_cargoa" for="inp_cargoa" >Técnico-Administrativo</label>
                    <input id="inp_cargoa" type="radio" name="cargo" value="administrativo" 
                           <?php 
                                if ($_SESSION['post']['config'] == 'administrativo') 
                                    echo 'checked="checked" disabled';
                                else if ($_SESSION['post']['config'] == 'docente')
                                    echo 'disabled';
                                else if ($_GET['ocup_id'] > '700000'  && $_GET['ocup_id'] != '')
                                    echo 'checked="checked"';
                                else if ($_SESSION['cargo'] != '707001' && $_SESSION['cargo'] != '')
                                    echo 'checked="checked"';
                           ?>
                           title="administrativo">
                </div>
                <div id="div_sexo" class="div_campo" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                    <label id="lbl_sexo" class="lbl_requirido" >Sexo *</label>
                    <label id="lbl_sexof" for="inp_sexof" >Feminino</label>
                    <input id="inp_sexof" type="radio" name="sexo" value="F" 
                           <?php if ($res_ins[0]['ins_sexo'] == 'F') echo 'checked="checked"'; ?>
                           title="Feminino">
                    <label id="lbl_sexom" for="inp_sexom" >Masculino</label>
                    <input id="inp_sexom" type="radio" name="sexo" value="M" 
                           <?php if (($res_ins[0]['ins_sexo'] === 'M' || $res_ins[0]['ins_sexo'] === null )) echo 'checked="checked"'; ?> 
                           title="Masculino">
                </div>
                </fieldset>
                <!-- Mostrar os dados pessoais que ele já digitou na autenticação, caso seja novo servidor -->
                <fieldset id="fds_preenchido" style="display: <?php if (!isset($_SESSION['post']['siape'])) echo "block"; else echo "none";?>;">
                <legend>Confira os seus Dados Pessoais</legend>
                <div id="div_dados" >
                    <label id="lbl_cpf" class="lbl_desc" title="CPF" >CPF:</label>
                    <label id="lbl_dadocpf" class="lbl_dados" title="CPF" /><?php echo $_SESSION['post']['cpf'];?></label>
                </div>
                <div id="div_dados" >
                    <label id="lbl_dtnasc" class="lbl_desc" title="Data de Nascimento" >Data de Nascimento:</label>
                    <label id="lbl_dadonasc" class="lbl_dados" title="Data de Nascimento" /><?php echo $_SESSION['post']['dtnasc']; ?></label>
                </div>  
                </fieldset>
            <fieldset id="fds_contato">
                <legend>Contato</legend>
                <div id="div_contato">
                    <div id="div_tel1" class="<?php if (isset($erros['tel1']['erro'])) echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                        <label id="lbl_tel1" for="inp_tel1">Telefone para Contato</label>
                        <img id="img_erro_tel1" alt="Erro!" src="resources/error.png" style="display: none;" />
                        <input id="inp_tel1" name="tel1" type="text" class="iMask" size="12" title="Telefone Principal"
                               value="<?php if (isset($res_ins[0]['ins_tel1']) && $res_ins[0]['ins_tel1'] != '') echo $res_ins[0]['ins_tel1']; else echo "(  )        ";?>"
                               alt="{type:'fixed',
                                    mask:'(99)999999999',
                                    stripMask:false}"/>
                    </div>
                    <div id="div_tel2" class="<?php if (isset($erros['tel2']['erro'])) echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                        <label id="lbl_tel2" for="inp_tel2">Telefone Secundário</label>
                        <img id="img_erro_tel2" alt="Erro!" src="resources/error.png" style="display: none;" />
                        <input id="inp_tel2" name="tel2" type="text" class="iMask" size="12" title="Telefone Secundário"
                               value="<?php if (isset($res_ins[0]['ins_tel2']) && $res_ins[0]['ins_tel2'] != '') echo $res_ins[0]['ins_tel2']; else echo "(  )        ";?>"
                               alt="{type:'fixed',
                                    mask:'(99)999999999',
                                    stripMask:false}"/>
                    </div>
                    <div id="div_email" class="<?php if (isset($erros['email']['erro'])) echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                        <label class="lbl_requirido" id="lbl_email" for="inp_email">E-mail *</label>
                        <img id="img_erro_email" alt="Erro!" src="resources/error.png" style="display: none;" />
                        <input id="inp_email" name="email" type="text" size="27" 
                               value="<?php if (isset($res_ins[0]['ins_email'])) echo $res_ins[0]['ins_email'];?>"
                               onfocus="input_focus(this)" onblur="input_blur(this)" title="E-mail"/>
                    </div>                
                </div>      
            </fieldset>
            <fieldset id="fds_inscricao">
                <legend>Inscrição</legend>
                <div id="div_inscricao">
                    <div id="div_origem" class="<?php if (isset($erros['origem']['erro']))echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                        <label class="lbl_origem" id="lbl_origem">Campus Origem *</label>
                        <img id="img_erro_origem" alt="Erro!" src="resources/error.png" 
                             style="display: <?php if (isset($erros['destino']['erro']))echo "inline"; else echo "none";?>;" /> 
                        <select id="origem" name="origem"  title="Selecione entre um dos Campus da lista"  
                                onfocus="input_focus(this)" onblur="input_blur(this)">
                        <?php
                            // Se não for alteração de inscrição
                            if (!isset($res_ins[0]['ins_origem']))
                                echo '<option disabled="disabled" selected="selected" value="false" >Selecione uma opção</option>';

                            foreach($res as $origem){
                                if (isset($res_ins[0]['ins_origem']) && $res_ins[0]['ins_origem'] == $origem['cam_id'])
                                    echo '<option class="opt_origem" selected="selected" value="'. $origem['cam_id'] .'" >'. 
                                                                                        $origem['cam_nome'] .'</option>'."\n";
                                else
                                    echo '<option class="opt_origem" value="'. $origem['cam_id'] .'" >'. 
                                                                                        $origem['cam_nome'] .'</option>'."\n";
                            }
                        ?>
                        </select>
                    </div>
                    <div id="div_destino" class="<?php if (isset($erros['destino']['erro']))echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                        <label class="lbl_destino" id="lbl_destino">Campus Destino *</label>
                        <img id="img_erro_destino" alt="Erro!" src="resources/error.png" 
                             style="display: <?php if (isset($erros['destino']['erro']))echo "inline"; else echo "none";?>;" /> 
                        <select id="destino" name="destino"  title="Selecione entre um dos Campus da lista"  
                                onfocus="input_focus(this)" onblur="input_blur(this)"
                                onchange="vagasDestino(destino)" onkeydown="vagasDestino(destino)">
                        <?php
                            echo '<option disabled="disabled" selected="selected" value="false" >Selecione uma opção</option>';
                            
                            if (isset($_GET['tipo']) && $_GET['tipo']){
                                // Listar somente vagas com permuta ou do edital quando o servidor selecionar
                                // as vagas da listagem
                                $strSQL = 'SELECT DISTINCT cam_id, cam_nome FROM campus' .
                                            ' JOIN vaga ON vaga_campus = cam_id' .
                                            ' WHERE vaga_' . $_GET['tipo'] .' > 0' .
                                            ' AND vaga_tipo ' . ($_GET['ocup_id'] < '700000' ? '< 700000' : '= ' . $_GET['ocup_id']) . 
                                            ' ORDER BY cam_nome';
                                $destinos = $bd->executarSQL($strSQL);
                                // Caso o usuário tente acessar vagas que não existem pelo url do navegador
                                if (count($destinos) <= 0){
                                ?>
                                    <script type="text/javascript" >   
                                        location.replace("/remocao/?lista");
                                    </script>
                                <?php
                                }  
                            }else
                                $destinos = $res; 
                            
                            foreach($destinos as $destino){
                                if (isset($res_ins[0]['ins_destino']) && $res_ins[0]['ins_destino'] == $destino['cam_id'])
                                    echo '<option class="opt_destino" selected="selected" value="'. $destino['cam_id'] .'" >'. 
                                                                                        $destino['cam_nome'] .'</option>'. "\n";
                                else if(isset($_GET['cam_id']) && $destino['cam_id'] == $_GET['cam_id'])
                                    echo '<option class="opt_destino" selected="selected" value="'. $destino['cam_id'] .'" >'. 
                                                                                        $destino['cam_nome'] .'</option>'. "\n";
                                else
                                    echo '<option class="opt_destino" value="'. $destino['cam_id'] .'" >'. 
                                                                                        $destino['cam_nome'] .'</option>'. "\n";
                            }
                            
                        ?>
                        </select>
                    </div>
                    <div id="div_vaga" class="<?php if (isset($erros['vaga']['erro']))echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                        <label class="lbl_vaga" id="lbl_vaga">Vaga a Concorrer *</label>
                        <img id="img_erro_vaga" alt="Erro!" src="resources/error.png"
                             style="display: <?php if (isset($erros['vaga']['erro']))echo "inline"; else echo "none";?>;" />
                        <select id="vaga" name="vaga"  
                                onfocus="input_focus(this)" onblur="input_blur(this)"
                                title="Selecione a Ocupação" 
                                onchange="montarText(this.value)" onkeydown="vagasDestino(destino)">
                        <?php
                            if (!isset($res_ins[0]['ins_destino'])){
                                // Nos casos em que o servidor não possui matrícula SIAPE
                                if ($_GET['ocup_id'] && isset($_SESSION['cargo'])
                                        && $_SESSION['cargo'] > '700000' 
                                        && $_SESSION['post']['config'] != 'docente' 
                                        && $_SESSION['post']['siape'] != '')
                                    $strSQL = 'SELECT ocup_id, ocup_nome FROM ocupacao ' .
                                                'WHERE ocup_id = ' . $_SESSION['cargo'];
                                else if ($_GET['ocup_id'])
                                    $strSQL = 'SELECT ocup_id, ocup_nome FROM ocupacao' .
                                                ' JOIN vaga ON vaga_tipo = ocup_id' .
                                                ' WHERE ocup_id ' . ($_GET['ocup_id'] < '700000' ? '<' : '>') . ' 700000' .
                                                ($_GET['ocup_id'] < '700000' ? ' AND ocup_id <> "499.999" AND ocup_id <> 000' : ' AND ocup_id <> 707001') .
                                                ' AND vaga_' . $_GET['tipo'] .' > 0' .
                                                ' AND vaga_campus = ' . $_GET['cam_id'] .
                                                ' ORDER BY ocup_nome';
                                else
                                    echo '<option disabled="disabled" selected="selected" value="false" >
                                            Selecione inicialmente o Campus Destino</option>';
                                
                                if ($_GET['ocup_id']){
                                    $vagas = $bd->executarSQL($strSQL);
                                    foreach($vagas as $vaga){
                                        // Montar select
                                        if ($_GET['ocup_id'] == $vaga['ocup_id'])
                                            echo '<option class="opt_vaga" value="'. $vaga['ocup_id'] .'" selected="selected" >'. 
                                                                                          $vaga['ocup_nome'] . '</option>'."\n";
                                        else 
                                            echo '<option class="opt_vaga" value="'. $vaga['ocup_id'] .'" >'. 
                                                                                          $vaga['ocup_nome'] .'</option>'."\n";
                                    }
                                }
                            }
                            // Carregar a select com a vaga ao alterar a inscrição
                            else {
                                // Para servidores administrativos em configurações normais e com ambos
                                if ($_SESSION['cargo'] > '700000' && $_SESSION['cargo'] != '707001' && $_SESSION['post']['siape'] != '')
                                    $querySQL = 'SELECT ocup_id, ocup_nome FROM ocupacao ' .
                                                'WHERE ocup_id = ' . $_SESSION['cargo'];
                                // Para o resto dos casos
                                else
                                    $querySQL = 'SELECT ocup_id, ocup_nome FROM ocupacao ' .
                                                'WHERE ocup_id ' . ($_SESSION['cargo'] == '707001' ? '<' : '>') . ' 700000' .
                                                ($_SESSION['cargo'] == '707001' ? ' AND ocup_id <> "499.999" AND ocup_id <> 000' : ' AND ocup_id <> 707001') .
                                                ' ORDER BY ocup_nome';
                                
                                $vagas = $bd->executarSQL($querySQL);
                                
                                // Capturar tipo da vaga
                                if (isset($res_ins[0]['ins_vaga_dest']) && $res_ins[0]['ins_vaga_dest']){
                                    $strSQL = "SELECT vaga_tipo FROM vaga WHERE vaga_id = " . $res_ins[0]['ins_vaga_dest'];
                                    $res_vag = $bd->executarSQL($strSQL);
                                }
                                foreach($vagas as $vaga){
                                    // Montar select
                                    if (isset($res_vag[0]['vaga_tipo']) && $res_vag[0]['vaga_tipo'] == $vaga['ocup_id'])
                                        echo '<option class="opt_vaga" value="'. $vaga['ocup_id'] .'" selected="selected" >'. 
                                                                                      $vaga['ocup_nome'] . '</option>'."\n";
                                    else 
                                        echo '<option class="opt_vaga" value="'. $vaga['ocup_id'] .'" >'. 
                                                                                      $vaga['ocup_nome'] .'</option>'."\n";
                                }
                            }    
                        ?>
                        </select>
                        <img class="img_erro" id="img_area" src="resources/progress_green_circle.gif" alt="Aguarde..." >
                    </div>
                    <div id="div_quantidade" style="display:none" class="div_campo" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)">
                        <label id="lbl_qtde" class="lbl_desc" title="Quantitativo" >Tipo de Vaga:</label>
                        <label id="lbl_quantidade" class="lbl_quantidade" title="quantidade" />Carregando...</label>
                    </div>  
                </div>
            </fieldset>
            <!-- Somente para docentes ou ambos -->
            <?php if ($_SESSION['post']['config'] != "administrativo"){ ?>
            <fieldset id="fds_anexo" >
                <legend>Anexos</legend>
                <div id="div_anexo" class="<?php if (isset($erros['anexo']['erro'])) echo "div_campo_erro"; else echo "div_campo_anexo";?>">
                    <label id="lbl_anexo" for="inp_anexo">Relacione os documentos que serão anexados ao processo: </label>
                    <img id="img_erro_anexo" alt="Erro!" src="resources/error.png" style="display: none;" />
                    <textarea id="inp_anexo" name="anexo" maxlength='1000' rows="4" cols="60" style="resize: none;"
                           title="Anexos"><?php if (isset($res_ins[0]['ins_anexo'])) echo $res_ins[0]['ins_anexo'];?></textarea>
                </div>
            </fieldset>
            <?php } ?>
            <fieldset id="fds_declaracao" >
                <legend>Declaração</legend>
                <div id="div_declaracao">
                    <!-- Alterar o link e descrição do edital se for docente ou administrativo -->
                    <?php if ($_SESSION['post']['config'] == "docente"){ ?>
                    <div id="div_citacao" >
                        Declaro que os dados acima informados são verdadeiros, e que aceito os termos do 
                        <a href="<?=$_SESSION['url_doc'];?>" target="_blank"><?=$_SESSION['edital_doc'];?>.</a>
                    </div>
                    <?php }else if ($_SESSION['post']['config'] == "administrativo"){ ?>
                    <div id="div_citacao" >
                        Declaro que os dados acima informados são verdadeiros, e que aceito os termos do Edital
                        <a href="<?=$_SESSION['url_adm'];?>" target="_blank"><?=$_SESSION['edital_adm'];?>.</a>
                    </div>
                    <?php }else{ ?>
                    <div id="div_citacao" >
                        Declaro que os dados acima informados são verdadeiros, e que aceito os termos dos Editais
                        <a href="<?=$_SESSION['url_adm'];?>" target="_blank"><?=$_SESSION['edital_adm'];?></a> e
                        <a href="<?=$_SESSION['url_doc'];?>" target="_blank"><?=$_SESSION['edital_doc'];?>.</a>
                    </div>
                    <?php } ?>
                    <div id="div_aceite" >
                        <input id="inp_aceite" type="checkbox" name="declaracao" value="aceito" title="Marque para aceitar" />
                        <label id="lbl_aceite" for="inp_aceite" >Concordo</label>
                    </div>
                </div>
            </fieldset>
            <div id="div_enviar">
                <button id="btn_enviar" style="display: none;">Enviar Formulário</button>
                <input id="inp_enviar" type="button" onclick="confirmarDados();" value="Enviar Formulário" style="display: inline;"
                       title="Enviar Formulário"/>
            </div>
        </form>
        <div id="div_notas" >
            <div id="div_nota" >
                <!-- Se o servidor não estiver no banco cadastro -->
                <?php if (!isset($_SESSION['post']['siape'])){ ?>
                <p id="p_nome" class="p_nota" >
                    <label for="inp_nome" class="label_erro" >Nome</label> - Digite o seu nome neste campo.
                </p><br/>
                <!-- Somente para ambos -->
                <?php if ($_SESSION['post']['config'] == "ambos"){ ?>
                <p id="p_cargo" class="p_nota" >
                    <label for="inp_cargo" class="label_erro" >Cargo</label> - Selecione o seu cargo neste campo.
                </p><br/>
                <?php } ?>
                <p id="p_sexo" class="p_nota" >
                    <label for="inp_sexo" class="label_erro" >Sexo</label> - Selecione o seu sexo neste campo.
                </p><br/>
                <?php } ?>
                <p id="p_tel1" class="p_nota" >
                    <label for="inp_tel1" class="label_erro" >Telefone para Contato</label> - Digite o DDD e os dígitos do telefone principal para contato.
                </p><br/>
                <p id="p_tel2" class="p_nota" >
                    <label for="inp_tel2" class="label_erro" >Telefone Secundário</label> - Digite o DDD e o respectivo número caso possua um telefone para contato secundário.
                </p><br/>
                <p id="p_email" class="p_nota" >
                    <label for="inp_email" class="label_erro" >E-mail</label> - Digite um e-mail válido para receber a confirmação do envio de sua inscrição.
                </p><br/>
                <p id="p_origem" class="p_nota" >
                    <label for="origem" class="label_erro" >Campus Origem</label> - Selecione neste campo o campus no qual está lotado atualmente.
                </p><br/>
                <p id="p_destino" class="p_nota" >
                    <label for="destino" class="label_erro" >Campus Destino</label> - Selecione neste campo o campus no qual possui interesse em lotar-se.
                </p><br/>
                <p id="p_vaga" class="p_nota" >
                    <label for="vaga" class="label_erro" >Ocupação</label> - Selecione neste campo a ocupação do campus destino na qual deseja concorrer.
                </p><br/>
                <!-- Somente para docentes ou ambos -->
                <?php if ($_SESSION['post']['config'] != "administrativo"){ ?>
                <p id="p_anexo" class="p_nota" >
                    <label for="inp_anexo" class="label_erro" >Relação de Documentos Anexados</label> - Digite neste campo uma descrição breve sobre todos os documentos que serão anexados na versão impressa do processo de remoção.
                </p><br/>
                <!-- Somente para docentes ou ambos -->
                <?php } if ($_SESSION['post']['config'] != "ambos"){ ?>
                <p id="p_declaracao" class="p_nota" >
                    <label for="inp_aceite" class="label_erro" >Declaração</label> - Assinale a caixa, declarando que os dados preenchidos são verdadeiros e que concorda com os termos do Edital.
                </p><br/>
                <!-- Somente para docentes ou ambos -->
                <?php }else{ ?>
                <p id="p_declaracao" class="p_nota" >
                    <label for="inp_aceite" class="label_erro" >Declaração</label> - Assinale a caixa, declarando que os dados preenchidos são verdadeiros e que concorda com os termos dos Editais, dependendo de qual for o seu tipo de cargo.
                </p><br/>
                <?php } ?>
            </div>
        </div>
    </div>
</div>