<meta http-equiv="Content-Type" content="text/html; charset=ANSI">
<?php

/*
 * Subir estração de dados do SIAPE para o banco de dados
 * Autor: Adriano A. B. Ramos
 */

require_once "../classes/banco.php";
require_once "../classes/utilidades.php";

// Conexão com o banco de dados
$bd = new banco("mysql:host=localhost;dbname=remocao", "cadonline", "123456");
$util = new utilidades();

// Mudar o nome do arquivo ao fazer uma nova extração
$file = @fopen("562782.TXT", 'r') 
        or die("Erro ao abrir o arquivo!");

// Verificar se o arquivo existe
if (is_bool($file))
    die("Erro ao ler o arquivo!");
// Continuar com a extração
else{
    // Tradução de palavras acentuadas
    $traduzir = array(
                'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Ä'=>'A', 'Æ'=>'AE', 'Ç'=>'C',
                'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ð'=>'Eth',
                'Ñ'=>'N', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O',
                'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',

                'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'å'=>'a', 'ä'=>'a', 'æ'=>'ae', 'ç'=>'c',
                'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'eth',
                'ñ'=>'n', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o',
                'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y',

                'ß'=>'sz', 'þ'=>'thorn', 'ÿ'=>'y'
            );

    // Limpar tabela existente usando comando executarSQL de banco.php
    $res = $bd->executarSQL("DELETE FROM `cadastro`");

    $sqlstr = "INSERT INTO cadastro(
    `cad_siape` ,
    `cad_nome` ,
    `cad_nasc` ,
    `cad_cpf` ,
    `cad_sexo` ,
    `cad_est_civil` ,
    `cad_habilitacao` ,
    `cad_situacao` ,
    `cad_cargo` ,
    `cad_regime` ,
    `cad_campus`
    ) VALUES";

    if($file)
    {
            while(!feof($file))
            {
                    $buffer = fgets($file, 4096);

                    //Extrair dados por substrings
                    $siape = substr($buffer, 5, 7);
                    $nome = ereg_replace("[^A-Za-z0-9 ]", "", $util->retirarAssentos(utf8_encode(substr($buffer, 12, 60)), $traduzir));
                    $data_nasc = substr($buffer, 72, 4) . '-' . substr($buffer, 76, 2) .
                            '-' . substr($buffer, 78, 2);
                    $cpf = substr($buffer, 80, 3) . '.' . substr($buffer, 83, 3) . '.' . 
                            substr($buffer, 86, 3) . '-' . substr($buffer, 89, 2);
                    $sexo = substr($buffer, 91, 1);
                    $estado_civil = substr($buffer, 92, 1);
                    $habilitacao = substr($buffer, 93, 3);
                    $sit_funcional = substr($buffer, 96, 5);
                    $cargo = substr($buffer, 104, 6);
                    $regime = substr($buffer, 110, 2);
                    $campus = substr($buffer, 119, 2);
                    switch ($campus) {
                        case 2:
                            $campus = 'REITORIA';
                            break;
                        case 3:
                            $campus = 'PROCURADORIA FEDERAL/REITORIA';
                            break;
                        case 4:
                            $campus = 'PRO-REITORIA DE DESENV INSTITUCIONAL';
                            break;
                        case 5:
                            $campus = 'PRO-REITORIA DE EXTENSAO';
                            break;
                        case 6:
                            $campus = 'PRO-REITORIA DE ADMINISTRACAO ';
                            break;
                        case 7:
                            $campus = 'PRO-REITORIA DE ENSINO';
                            break;
                        case 8:
                            $campus = 'PRO-REITORIA DE PESQUISA E INOVACAO';
                            break;
                        case 9:
                            $campus = 'AUDITORIA INTERNA/REITORIA';
                            break;
                        case 10:
                            $campus = 'CAMPUS SAO PAULO';
                            break;
                        case 13:
                            $campus = 'CAMPUS CUBATAO';
                            break;
                        case 15:
                            $campus = 'CAMPUS SERTAOZINHO';
                            break;
                        case 17:
                            $campus = 'CAMPUS GUARULHOS';
                            break;
                        case 19:
                            $campus = 'CAMPUS SAO JOAO B VISTA ';
                            break;
                        case 21:
                            $campus = 'CAMPUS CARAGUATATUBA';
                            break;
                        case 23:
                            $campus = 'CAMPUS BRAGANCA PAULISTA';
                            break;
                        case 25:
                            $campus = 'CAMPUS SALTO';
                            break;
                        case 27:
                            $campus = 'CAMPUS SAO ROQUE';
                            break;
                        case 29:
                            $campus = 'CAMPUS SAO CARLOS';
                            break;
                        case 31:
                            $campus = 'CAMPUS CAMPOS DO JORDAO';
                            break;
                        case 33:
                            $campus = 'CAMPUS BARRETOS';
                            break;
                        case 35:
                            $campus = 'CAMPUS SUZANO';
                            break;
                        case 37:
                            $campus = 'CAMPUS CAMPINAS';
                            break;
                        case 39:
                            $campus = 'CAMPUS CATANDUVA';
                            break;
                        case 41:
                            $campus = 'CAMPUS AVARE';
                            break;
                        case 43:
                            $campus = 'CAMPUS ARARAQUARA';
                            break;
                        case 45:
                            $campus = 'CAMPUS ITAPETININGA';
                            break;
                        case 47:
                            $campus = 'CAMPUS BIRIGUI';
                            break;
                        case 48:
                            $campus = 'CAMPUS VOTUPORANGA';
                            break;
                        case 49:
                            $campus = 'CAMPUS REGISTRO';
                            break;
                        case 50:
                            $campus = 'CAMPUS PRES EPITACIO';
                            break;
                        case 51:
                            $campus = 'CAMPUS PIRACICABA';
                            break;
                        case 52:
                            $campus = 'CAMPUS HORTOLANDIA';
                            break;
                        case 53:
                            $campus = 'CAMPUS S.JOSE DOS CAMPOS';
                            break;
                        case 54:
                            $campus = 'CAMPUS BOITUVA';
                            break;
                        case 55:
                            $campus = 'CAMPUS MATAO';
                            break;
                        case 56:
                            $campus = 'CAMPUS CAPIVARI';
                            break;
                        default:
                            $campus = 'NAO INFORMADO';
                            break;
                    }
                    // Caso a linha não esteja vazia, criar string de comando
                    if ($siape != NULL)
                    {
                        // Comando para inserção
                        $sqlstr .= <<< EOL
                        ("$siape", "$nome", "$data_nasc", "$cpf", "$sexo", $estado_civil, 
                            "$habilitacao", "$sit_funcional", "$cargo", $regime, "$campus"),
EOL;
                    }
            }
    // Inserindo servidores para testes 
    $sqlstr .= ' ("9999999", "SERVIDOR DOCENTE TESTE ", "1985-01-01", "227.079.762-02", "M", 2, "105", "EST01", "707001", 40, "REITORIA"), ("8888888", "SERVIDOR ADMINISTRATIVO TESTE ", "1985-02-02", "439.462.966-70", "F", 2, "000", "EST01", "701200", 40, "CAMPUS CAPIVARI")';
    fclose($file);
    // Utiliza comando executarSQL de banco.php
    $res = $bd->executarSQL($sqlstr);
    echo "Extracao do SIAPE gravada com sucesso!<br>Mes/Ano da extracao: ";
    // Inserir mês da extração nas configurações
    $sqlstr  = "UPDATE `configuracoes` SET";
    $sqlstr .= " config_extracao = ";
    // Data com mês e ano
    $periodo = date("m/20y");
    $sqlstr .= <<< EOL
    ("$periodo")
EOL;

    $bd->executarSQL($sqlstr);
    echo $periodo;
    }
}
?>