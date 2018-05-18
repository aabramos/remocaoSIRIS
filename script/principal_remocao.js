// Opções do recaptcha
var RecaptchaOptions = {
    theme : 'white',
    lang : 'pt',
    tabindex : 4
};

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
    var img_erro = document.getElementById("img_erro_"+inp.name);
    img_erro.style.display = "inline";
    img_erro.parentNode.className = "div_campo_erro";
}

function retirarErro(inp){
    var img_erro = document.getElementById("img_erro_"+inp.name);
    img_erro.style.display = "none";
    img_erro.parentNode.className = "div_campo";
}

function criarErro(campo, nome, mensagem){
    var div_erro = document.createElement("DIV");
    div_erro.className = "div_nota";
    div_erro.setAttribute("id", "div_nota_" + campo.id);
    var label = document.createElement("LABEL");
    label.setAttribute("for", campo.id);
    label.className = "label_erro";
    label.appendChild(document.createTextNode(nome));
    div_erro.appendChild(label);
    div_erro.appendChild(document.createTextNode(mensagem));
    document.getElementById("div_notas").appendChild(div_erro);
}

function removerErros(campo){
    var notas = document.getElementById("div_notas");
    var divs = notas.getElementsByTagName("DIV");
    if (divs.length > 0){
        while(divs.length > 0){
            notas.removeChild(divs_erro[0]);
        }
    }
}

function dataInvalida(valor){
    array_data = valor.split("/");
    if (array_data.length < 3) return true;
    dia = array_data[0] * 1;
    // javascript considera meses de 0 a 11
    mes = array_data[1] - 1;
    ano = array_data[2] * 1;
    obj_data = new Date(ano, mes, dia);
    
    // Limite de idade: 15 a 69 anos
    if (ano < 1943 || ano > 1997) return true;
    if (ano != obj_data.getFullYear()) return true;
    if (mes != obj_data.getMonth()) return true;
    // Para o problema do 20~21 do Objeto Date do Javascript
    if (dia == obj_data.getDate() + 1) return false;
    if (dia != obj_data.getDate()) return true;
    
    return false;
}

function confirmarDados(form){
    erro = false;
    erros = false;
    campos = form.elements;
    campo = false;

    removerErros();  

    for (i = 0; i < campos.length; i++){
        switch(campos[i].name){
            case 'siape':
                campo = true;
                if (campos[i].value == "" && document.getElementById('novo').checked == false) {
                    erro = true;
                    criarErro(campos[i], "[" + campos[i].title + "]", " - Este campo é obrigatório e deve ser preenchido. Caso ainda não possua um número de matrícula, dê um clique na caixa de seleção.");
                } else if (!validaSIAPE(campos[i].value) && document.getElementById('novo').checked == false) {
                    erro = true;
                    criarErro(campos[i], "[" + campos[i].title + "]", " - O número da Matrícula SIAPE deve possuir exatamente 7 dígitos.");
                }
                break;
            case 'dtnasc':
                campo = true;
                if (campos[i].value == "DD/MM/AAAA" || campos[i].value == "") {
                    erro = true;
                    criarErro(campos[i], "[Data de Nascimento]", " - Clique no ícone de calendário a direita do campo, selecione o ano de nascimento, navegue com as setas até seu mês de nascimento e clique no dia de seu nascimento.");
                } else if (dataInvalida(campos[i].value)) {
                    erro = true;
                    criarErro(campos[i], "[Data de Nascimento]", " - Data inválida! Clique no ícone de calendário a direita do campo, selecione o ano de nascimento, navegue com as setas até seu mês de nascimento e clique no dia de seu nascimento.");
                }
                break;            
	    case 'cpf':
                campo = true;
                if (!validaCPF(campos[i].value)) {
                    erro = true;
                    criarErro(campos[i], "[CPF]", " - O CPF informado está incompleto ou é inválido.");
                }
                break;
            case 'recaptcha_response_field':
                campo = true;
                if (campos[i].value == "") {
                    erro = true;
                    criarErro(campos[i], "[reCAPTCHA]", " - Digite as palavras que aparecem na figura. Utilize os botões para trocar a imagem, caso tenha dificuldades.");
                }
                break;
        }
        if (erro){
            erro = false;
            campo = false;
            erros = true;
            if (campos[i].name != 'recaptcha_response_field' )
                mostrarErro(campos[i]);
        } else if (campo) {
            campo = false;
            if (campos[i].name != 'recaptcha_response_field' )
                retirarErro(campos[i]);
        }
    }
    div_nota = document.getElementById("div_notas");
    divs_erro = div_nota.getElementsByTagName("DIV");

    if (divs_erro.length > 0) erros = true;    

    return (!erros);
}

function enviarForm(form){
    resposta = confirmarDados(form);

    if (resposta){
        alertify.success("Carregando...");
        return true;
    }else {
        alertify.alert("Atenção: Corrija os erros do formulário!");
        return false;
    }
}

function visibilidade(id) {
    if(document.getElementById(id).style.display == 'none')
       document.getElementById(id).style.display = 'block';
    else
       document.getElementById(id).style.display = 'none';
}

function desativarCampo(id) {
    if(document.getElementById(id).checked == false) 
        document.frm_inscricao.siape.disabled = false;
    else {
        document.frm_inscricao.siape.disabled = true;
        document.frm_inscricao.siape.value = "";
    }
}