<?php 
    // Chave para 10.2.6.24, deve-se usar a outra quando for por no ar
    $chavePublica = "6Le7yPcSAAAAAG6w8yhQKuXSnPA2Od89oMUTpHbM";
    $captchaErro = (isset($_SESSION["catpchaErro"]))?$_SESSION["catpchaErro"]:null;

    // Tomar as ações quando houverem erros vindos de cadastro_remocao
    if (isset($_SESSION["erro"])){
        if ($_SESSION["erro"]){
            $erros = $_SESSION["invalidos"];
            $valores = $_SESSION["post"];
        }
        else{
            $valores = $_SESSION["post"];
        }
    }
?>
<div id="div_body">
    <div id="div_formulario" class="formulario" >
        <form name="frm_inscricao" method="post" action="cadastro_validacao.php" onsubmit="return enviarForm(this)" >
            <input type="hidden" value="1" name="envio"/>
            <?php if (isset($_SESSION["erro"])) {echo '<input id="inp_erro" type="hidden" value="1">'; unset($_SESSION["erro"]);} ?>
            <fieldset id="fds_pessoal">
                <legend>Acesso ao Sistema</legend>
                <div id="div_siape" class="<?php if (isset($erros["siape"]["erro"]))echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                    <label id="lbl_siape" class="lbl_requerido" for="inp_siape">Matrícula SIAPE * 
                        <a href="#" style="text-decoration:none" onclick="visibilidade('div_holerite');">
                        <font color="2E892A" size="2px">Clique aqui para visualizar as instruções</font></a></label>
                    <img class="img_erro" id="img_erro_siape" alt="Erro!" src="resources/error.png" 
                         style="display: <?php if (isset($erros["siape"]["erro"]))echo 'inline'; else echo 'none';?>;" />
                    <input id="inp_siape" type="text" name="siape" class="iMask" size="6" title="Matrícula SIAPE" tabindex="1"
                           value="<?php if (isset($valores["siape"]))echo $valores["siape"]; else echo "";?>"
                           alt="{type:'fixed',
                                mask:'9999999',
                                stripMask:false}"/>
                </div>
                <div id="div_novo">
                    <input type="checkbox" id="novo" tabindex="0" name="novo"
                        onclick="desativarCampo('novo')" />
                    <label id="lbl_novo" for="novo" >Ainda não possuo um número de matrícula<br></label>
                </div>
                <div id="div_holerite" style="display:none;">
                    <ul class="ul_info" style="padding:10px; padding-left:32px;">
                        <li>Verifique a capa de seu holerite, a sua identidade funcional ou acesse o 
                            <a href="https://www1.siapenet.gov.br/servidor/public/pages/security/acesso.jsf" target="_blank" >
                            Portal SIAPEnet</a> para obter a Matrícula SIAPE:</li>
                    </ul>
                        <a href="#" style="padding:57px;" onclick="visibilidade('div_holerite');">
                        <img src="resources/holerite.jpg" alt="Holerite">
                    </a>
                </div>
                <div id="div_cpf" class="<?php if (isset($erros["cpf"]["erro"]))echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                    <label id="lbl_cpf" class="lbl_requerido" for="inp_cpf">CPF *</label>
                    <img class="img_erro" id="img_erro_cpf" alt="Erro!" src="resources/error.png" 
                         style="display: <?php if (isset($erros["cpf"]["erro"]))echo "inline"; else echo "none";?>;" />
                    <input id="inp_cpf" type="text" name="cpf" class="iMask" size="14" title="CPF" tabindex="2"
                           value="<?php if (isset($valores["cpf"]))echo $valores["cpf"]; else echo "   .   .   -  ";?>"
                           alt="{type:'fixed',
                                mask:'999.999.999-99',
                                stripMask:false}"/>
                </div>       
                <div id="div_dtnasc" class="<?php if (isset($erros["dtnasc"]["erro"]))echo "div_campo_erro"; else echo "div_campo";?>" onmouseover="div_campo_mouse_over(this)" onmouseout="div_campo_mouse_out(this)" >
                    <label id="lbl_dtnasc" class="lbl_requerido" for="inp_dtnasc">Data de Nascimento *</label>
                    <img class="img_erro" id="img_erro_dtnasc" alt="Erro!" src="resources/error.png" 
                         style="display: <?php if (isset($erros["dtnasc"]["erro"]))echo "inline"; else echo "none";?>;" />
                    <img class="img_datepicker" src="resources/datepicker_icon.jpg" style="display: inline;"
                         alt="Selecione a Data de Nascimento" onclick="displayDatePicker('dtnasc');" />
                    <input id="inp_dtnasc" type="text" name="dtnasc" class="iMask" size="9" title="Data de Nascimento" tabindex="3"
                           value="<?php if (isset($valores["dtnasc"]))echo $valores["dtnasc"]; else echo "DD/MM/AAAA";?>"
                           alt="{type:'fixed',
                                mask:'99/99/9999',
                                stripMask:false}"/>
                </div>
                <div class="div_campo" id="div_captcha">
                    <?php echo recaptcha_get_html($chavePublica, $captchaErro); ?>
                </div>
            </fieldset>
            <div id="div_enviar">
                <input id="inp_enviar" type="submit" value="Autenticação" title="Autenticação"/>
            </div>
        </form>
        <div id="div_notas" >
                <?php 
                if (isset($erros)){
                    foreach ($erros as $campo => $erro){
                        echo '<div class="div_nota" >';
                        echo '<label class="label_erro" for="';
                        switch ($campo){
                            case 'siape':
                                if ($erro["tipo"] == 1)
                                    echo 'inp_siape">[Matrícula SIAPE]</label> - Este campo é obrigatório e deve ser preenchido. Caso ainda não possua um número de matrícula, clique na caixa de seleção correspondente.';
                                else if ($erro["tipo"] == 2)
                                    echo 'inp_siape">[Matrícula SIAPE]</label> - O número de matrícula informado não foi localizado no cadastro do Sistema SIAPE ou o servidor não está apto a se inscrever no processo seletivo atual.';
                                else if ($erro["tipo"] == 3)
                                    echo 'inp_siape">[Matrícula SIAPE]</label> - O Servidor informado não está apto a se inscrever no processo seletivo atual, pois o mesmo se destina restritamente à docentes.';
                                else if ($erro["tipo"] == 4)
                                    echo 'inp_siape">[Matrícula SIAPE]</label> - O Servidor informado não está apto a se inscrever no processo seletivo atual, pois o mesmo se destina restritamente à técnicos-administrativos.';
                                else
                                    echo 'inp_siape">[Matrícula SIAPE]</label> - O CPF informado já possui uma Matrícula SIAPE associada. Caso não possua o número, entre em contato com o RH de seu campus para obtenção.';
                                break;
                            case 'cpf':
                                if ($erro["tipo"] == 1)
                                    echo 'inp_cpf">[CPF]</label> - O CPF informado está incompleto.';
                                else if ($erro["tipo"] == 2)
                                    echo 'inp_cpf">[CPF]</label> - O CPF informado é inválido.';
                                else 
                                    echo 'inp_cpf">[CPF]</label> - O CPF informado está incorreto ou não consta no Sistema SIAPE. Verifique o número e digite-o novamente.';
                                break;
                            case 'dtnasc':
                                if ($erro["tipo"] == 1)
                                    echo 'inp_dtnasc">[Data de Nascimento]</label> - Informe sua data de nascimento no formato especificado.';
                                else
                                    echo 'inp_dtnasc">[Data de Nascimento]</label> - A data de nascimento informada está incorreta ou não consta no Sistema SIAPE. Verifique a data e digite-a novamente.';
                                break;
                            case 'reCaptcha':
                                echo 'recaptcha_response_field">[Verificação]</label> - Digite as palavras como aparecem na figura.';
                                break;
                            case 'encerrado':
                                echo '">[Inscrições Encerradas]</label> - O período de inscrições está encerrado.';
                                break;
                            case 'manutenção':
                                echo '">[Sistema em Manutenção]</label> - O sistema se encontra em manutenção com retorno previsto para ' . (date("H") + 2) . ':00.';
                                break;
                        }
                        echo '</div>';
                    }
                }
                ?>
        </div>
    </div>
</div>