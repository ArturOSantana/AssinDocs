<?php

class Conexao
{
    private static $instance = null;

    public static function getConexao()
    {
        if (self::$instance === null) {
            try {
                $host    = "localhost";
                $dbname  = "assindocs";
                $usuario = "root";
                $senha   = "root";
                $charset = "utf8mb4";
                $porta   = "3306";

                $dsn = "mysql:host=$host;port=$porta;dbname=$dbname;charset=$charset";

                self::$instance = new PDO($dsn, $usuario, $senha);

                // Configurações de segurança e padrão
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            } catch (PDOException $e) {
                die("Erro na conexão com o banco: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
