<?php

class banco {

    private $dsn;
    private $dbh;

    /**
     * Construtor da classe banco.
     * <p> Conecta ao banco de dados.
     * </p>
     * @param string $bd Nome do Banco de Dados para a conexão.
     * @param string $user Usuário que vai conectar ao banco.
     * @param string $passwd Senha do usuário.
     */
    public function __construct($connStr, $user, $passwd) {
        $this->dsn = $connStr;
        $this->usuario = $user;
        $this->senha = $passwd;
    }

    /**
     * Executa consulta SQL diretamente.
     * O Método recebe uma string $consulta e retorna o seu resultado.
     * @param string $consulta string com o código SQL.
     * @return array|mixed
     */
    public function executarSQL($consulta) {

        try {
        $this->dbh = new PDO($this->dsn, $this->usuario, $this->senha);
        $pds = $this->dbh->prepare($consulta);

        // Informa ao banco para retornar apenas caracteres codificados em UTF8
        $this->dbh->query('SET NAMES utf8');
        $pds->execute();
        $res = $pds->fetchAll(PDO::FETCH_ASSOC);
        $this->dbh = null;
            
        } catch (Exception $exc) {
            $res = $exc->getMessage();
        }
        return $res;
    }
}
?>