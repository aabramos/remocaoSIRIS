// Capturar os $_GET como variáveis globais
var parts = window.location.search.substr(1).split("&");
var $_GET = {};
for (var i = 0; i < parts.length; i++) {
    var temp = parts[i].split("=");
    $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
}

window.onload = function carregarFormacao() {       
    if (document.getElementById("vaga").options[document.getElementById("vaga").selectedIndex].value != 'false'){
        montarText(document.getElementById("vaga").options[document.getElementById("vaga").selectedIndex].value);
        if (!$_GET['cam_id'])
            document.getElementById('div_quantidade').style.display = 'none';             
    }  
    if ($_GET['cam_id'] && document.getElementById("vaga").options[document.getElementById("vaga").selectedIndex].value == 'false')
        vagasDestino(destino);
}

function div_campo_mouse_over(div){
    if (div.className!="div_campo_selected" && div.className!="div_campo_erro")
        div.className = "div_campo_over";    
}

function div_campo_mouse_out(div){
    if (div.className!="div_campo_selected" && div.className!="div_campo_erro")
        div.className = "div_campo";    
}

function input_focus(inp){
    if (inp.parentNode.hasAttribute("class"))
        inp.parentNode.className = "div_campo_selected";
}

function input_blur(inp){
    if (inp.parentNode.hasAttribute("class"))
        inp.parentNode.className = "div_campo";
}

function mostrarErro(inp){
    var campo = document.getElementById("img_erro_"+inp);
    campo.style.display = "inline";
    campo.parentNode.className = "div_campo_erro";
}

function retirarErro(inp){
    var campo = document.getElementById("img_erro_"+inp);
    campo.style.display = "none";
    campo.parentNode.className = "div_campo";
}

function vagasDestino(destino){
    if (destino.selectedIndex){
        if (window.XMLHttpRequest){
            // códgio para IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
        else{
            // código para IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        // Atua somente quando estiver pronto
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                vaga = document.frm_remocao.vaga;
                resp_vaga = eval("(" + xmlhttp.responseText + ")");
                if (resp_vaga.erro) {
                    vaga.innerHTML = "";
                    option_erro = document.createElement("OPTION");
                    option_erro.innerHTML = resp_vaga.mensagem;
                    vaga.appendChild(option_erro);
		    document.getElementById("img_area").style.display = "none";
		    document.frm_remocao.vaga.disabled = "";
		    document.frm_remocao.vaga.disabled = false;
                }
                else{
                    montarSelect(resp_vaga);
                    montarText(vaga.options[vaga.selectedIndex].value);
		    document.getElementById("img_area").style.display = "none";
		    document.frm_remocao.vaga.disabled = false;
                    // Deixar invisível o campo de quantidade de vagas somente no caso de criação de permutas
                    if (!$_GET['cam_id'] && document.getElementById('div_quantidade').style.display == 'block' && document.getElementById('fds_novo').style.display == 'block')
                        document.getElementById('div_quantidade').style.display = 'none';
		}
            }
        };
        cargo = capturaCargo();
	document.getElementById("img_area").style.display = "block";
	document.frm_remocao.vaga.disabled = true;
        // Levar em consideração o valor do banco de dados, e não do índice
        parametros = "?req=vaga&config=" + cargo + "&tipo=" + $_GET['tipo'] + "&cam_id=" + destino.options[destino.selectedIndex].value;
        xmlhttp.open("GET","ajax/consultaBanco.php" + parametros, true);
        xmlhttp.send();
    }
}

function capturaCargo(){
    // Verificar qual cargo foi selecionado pelo servidor
    for (i = 0; i < document.getElementsByName('cargo').length; i++) {
        if(document.getElementsByName('cargo')[i].checked == true) {
            var cargo = document.getElementsByName('cargo')[i].value;
            break;
        }
    }
    return cargo;
}

function montarSelect(vagas){
    ocupacao = vaga.options[vaga.selectedIndex].value;
    vaga = document.frm_remocao.vaga;
    vaga.innerHTML = "";
    option = document.createElement("OPTION");
    option.value = false;
    if (vagas.length > 1 && !$_GET['tipo']){
        option.innerHTML = "Selecione a Ocupação";
        option.setAttribute("selected", "selected");
        option.setAttribute("disabled", "disabled");
        vaga.appendChild(option);
    }
    else if (vagas.length <= 0)
        option.innerHTML = "Não há vagas disponíveis";

    for (i = 0; i < vagas.length ; i++){
        option = document.createElement("OPTION");
        option.value = vagas[i].ocup_id;
        option.innerHTML = vagas[i].ocup_nome;
        // Caso o cargo/área seja puxada do banco de dados
        if (vagas.length == 1)
            montarText(option.value);
        vaga.appendChild(option);
    }
    if (ocupacao < 700000 && document.getElementById('fds_novo').style.display == 'none')
        document.getElementById('vaga').value = ocupacao;
}

// Atualizar o quantitativo de vagas ao selecionar a vaga
function montarText(selecao){
    if (window.XMLHttpRequest){
            // códgio para IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
        }
        else{
            // código para IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        // Atua somente quando estiver pronto
        xmlhttp.onreadystatechange=function(){
            if (xmlhttp.readyState==4 && xmlhttp.status==200){
                quantidade = document.frm_remocao.quantidade;
                resp_qtde = eval("(" + xmlhttp.responseText + ")");
                if (resp_qtde.erro) {
                    quantidade.innerHTML = "";
                    document.getElementById("lbl_quantidade").innerHTML = resp_qtde.mensagem;
                }
                else{
                    document.getElementById("lbl_quantidade").innerHTML = resp_qtde[0].qtde;
		}
            }
        };
        // Levar em consideração o valor do banco de dados, e não do índice
        parametros = "?req=quantidade&ocup_id=" + selecao + "&cam_id=" + destino.options[destino.selectedIndex].value;
        xmlhttp.open("GET","ajax/consultaBanco.php" + parametros, true);
        xmlhttp.send();
        // Deixar visível o campo de quantidade de vagas, se ainda não estiver
        if(document.getElementById('div_quantidade').style.display == 'none')
            document.getElementById('div_quantidade').style.display = 'block';
}

function confirmarDados(){
    var erros = false;
    var elementStyle = getStyle(document.getElementById("fds_novo"), "display");
    campos = frm_remocao.elements;
    
    /* E-mail */
    if (!validaEmail(document.frm_remocao.email.value)){
        erros = true;
        mostrarErro("email");
    } else 
        retirarErro("email");
    
    /* Nome */
    if (elementStyle == "block"){
        if ((document.frm_remocao.nome.value) == ""){
            erros = true;
            mostrarErro("nome");
        } else
            retirarErro("nome");
    }
    
    for (i = 0; i < campos.length; i++){
    switch(campos[i].name){
        /* Campus destino */
        case 'destino':
            if (campos[i].options[campos[i].selectedIndex].value == "false") {
                erros = true;
                mostrarErro("destino");
            } else if (document.frm_remocao.destino.value == document.frm_remocao.origem.value){
                erros = true;
                mostrarErro("destino");
                mostrarErro("origem");
            }
            else 
                retirarErro("destino");
            break;
         /* Campus origem */
        case 'origem':
            if (campos[i].options[campos[i].selectedIndex].value == "false") {
                erros = true;
                mostrarErro("origem");
            }
            else 
                retirarErro("origem");
            break;
        /* Vagas */
        case 'vaga':
            if (campos[i].options[campos[i].selectedIndex].value == "false" && campos[i].options.length > 0) {
                erros = true;
                mostrarErro("vaga");
            }
            else
                retirarErro("vaga");
            break;
        }
    }
    
    if (!erros) {
        /* Declaração */
        if (!document.frm_remocao.inp_aceite.checked){
            alertify.set({ labels : { ok: "Sim", cancel: "Não" } });
            alertify.confirm("Você deve estar de acordo com a declaração para enviar o requerimento. Está de acordo?", function (confirmar){
                if (confirmar){    
                    document.frm_remocao.inp_aceite.checked = true;
                    confirmaDeclaracao();
                }else{
                    alertify.set({ labels : { ok: "Ok", cancel: "Cancelar" } });
                    alertify.alert("Atenção: Corrija os erros do formulário!");
                }
            }); 
        } else{
            confirmaDeclaracao();
        }   
    }else{
        alertify.set({ labels : { ok: "Ok", cancel: "Cancelar" } });
        alertify.alert("Atenção: Corrija os erros do formulário!");
    }
}

function confirmaDeclaracao(){
    alertify.set({ labels : { ok: "Sim", cancel: "Não" } });
    if ($_GET['alterar']){
        var alerta = "A alteração da sua inscrição irá excluir a sua inscrição anterior. Deseja continuar?";
    }else{
        var alerta = "Tem certeza de que todos os dados estão corretos e deseja enviá-los?";
    }
    alertify.confirm(alerta, function (confirmar){
        if (confirmar){
            alertify.success("Por favor, aguarde...");
            document.forms["frm_remocao"].submit();
        }else{
            alertify.set({ labels : { ok: "Ok", cancel: "Cancelar" } });
            alertify.alert("Atenção: Corrija os erros do formulário!");
        }
    });
}

function getStyle(oElm, strCssRule){
    var strValue = "";
    if(document.defaultView && document.defaultView.getComputedStyle){
        strValue = document.defaultView.getComputedStyle(oElm, "").getPropertyValue(strCssRule);
    }
    else if(oElm.currentStyle){
        strCssRule = strCssRule.replace(/\-(\w)/g, function (strMatch, p1){
            return p1.toUpperCase();
        });
        strValue = oElm.currentStyle[strCssRule];
    }
    return strValue;
}