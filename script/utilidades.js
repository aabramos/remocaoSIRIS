function soNumeros(e){
   var key = (e.which)?e.which:e.keyCode;
   return (key >= 8 && key <= 57);
}

function validaEmail(email){
    return (/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-])+(\.[A-Za-z]{2,4}){1,3}$/).test(email);
}

function validaCPF(cpf){
    // Deve ter exatos 11 números, não importa se houver pontos e traço
    cpfnums = cpf.replace(/(\.)+/g,"").replace("-","");
    if (cpfnums.length != 11) return false;
    // Evitar CPFs com números repetidos. Ex: 000.000.000-00, 111.111.111-11, etc
    if ( cpfnums.search(new RegExp("[" + cpfnums.charAt(0) + "]{11}")) > -1 ) return false;

    // Cálculo do primeiro dígito
    sumDig1 = 0;
    for(var i = 10; i > 1; i--){
        sumDig1 += cpfnums[10-i] * i;
    }
    dig1 = (sumDig1 % 11 > 1)?11 - (sumDig1 % 11):0;

    // Cálculo do segundo dígito
    sumDig2 = dig1 * 2;
    for (i = 11; i > 2; i--){
        sumDig2 += cpfnums[11-i] * i;
    }
    dig2 = (sumDig2 % 11 > 1)?11 - (sumDig2 % 11):0;

    return (cpfnums[9] == dig1 && cpfnums[10] == dig2)?true:false;
}

function validaSIAPE(siape){
    // Deve ter exatos 7 números
    siapenums = siape.replace(/(\.)+/g,"").replace(" ","");
    if (siapenums.length != 7) return false;
    return true;
}

function validaNIS(nis){
    // Deve ter exatos 11 números, não importa se houver pontos, traços ou espaços
    nisnums = nis.replace(/[\.]?/g,"").replace(/[\-]?/g,"").replace(/[\ ]?/g,"");
    if (nisnums.length != 11) return false;
    // Evitar NIS com números repetidos. Ex: 000.000.000-0, 111.111.111-1, etc
    if ( nisnums.search(new RegExp("[" + nisnums.charAt(0) + "]{11}")) > -1 ) return false;

    // Multiplicador
    mtpl = new Array(3,2,9,8,7,6,5,4,3,2);

    // Cálculo do primeiro dígito
    sumDig = 0;
    for(var i = 0; i < 10; i++){
        sumDig += nisnums[i] * mtpl[i];
    }
    digito = (sumDig % 11 > 1)?11 - sumDig % 11:0;

    return (nisnums[10] == digito)?true:false;
}