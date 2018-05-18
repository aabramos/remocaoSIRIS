            <div id="div_footer">
                <div id='div_extracao' >
                    <p>Baseado no sistema Cadonline desenvolvido por Willian Martins Costa</p>
                    <p>Usando Extração do SIAPE referente ao mês de: <?php if (isset($footer)) echo $footer[0]['config_extracao'] ?></p>
                </div>
                <div id='div_versao' >
                    Sistema versão <?php if (isset($footer)) echo $footer[0] ['config_versao'] ?> desenvolvido por Adriano Alberto Borges Ramos
                </div>
                <div id="div_sessao">
                    <?php
                        if (isset($_SESSION['post']['cpf']) && isset($footer) && $_SESSION['post']['cpf'] != '')
                            echo "Identificação da Sessão: " . str_replace(array(" ", "-", ":"), "", $footer[0]['ins_data_hora']); 
                    ?>
                </div>                                      
            </div>
        </div>
    </body>
</html>