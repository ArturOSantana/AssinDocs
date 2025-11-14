<?php
require_once __DIR__ . '/Conexao.php';

class Auditoria {
    private $conn;

    public function __construct() {
        $this->conn = Conexao::getConexao();
    }

    /**
     * Registrar ação na auditoria
     */
    public function registrar($acao, $tipo, $usuario_id = null, $documento_id = null, $descricao = '', $metadata = []) {
        try {
            $sql = "INSERT INTO logs_auditoria 
                    (usuario_id, acao, tipo, documento_id, descricao, ip_address, user_agent, metadata) 
                    VALUES (:usuario_id, :acao, :tipo, :documento_id, :descricao, :ip, :ua, :metadata)";

            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':usuario_id' => $usuario_id,
                ':acao' => $acao,
                ':tipo' => $tipo,
                ':documento_id' => $documento_id,
                ':descricao' => $descricao,
                ':ip' => $_SERVER['REMOTE_ADDR'],
                ':ua' => $_SERVER['HTTP_USER_AGENT'],
                ':metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE)
            ]);

            return $result;

        } catch (Exception $e) {
            error_log("Erro ao registrar auditoria: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar logs de auditoria com filtros
     */
    public function buscarLogs($filtros = [], $limit = 100, $offset = 0) {
        try {
            $sql = "SELECT la.*, u.nome as usuario_nome, u.email as usuario_email, 
                           d.titulo as documento_titulo
                    FROM logs_auditoria la
                    LEFT JOIN usuarios u ON la.usuario_id = u.id
                    LEFT JOIN documentos d ON la.documento_id = d.id
                    WHERE 1=1";
            
            $params = [];
            $conditions = [];

            // Aplicar filtros
            if (!empty($filtros['usuario_id'])) {
                $conditions[] = "la.usuario_id = :usuario_id";
                $params[':usuario_id'] = $filtros['usuario_id'];
            }

            if (!empty($filtros['tipo'])) {
                $conditions[] = "la.tipo = :tipo";
                $params[':tipo'] = $filtros['tipo'];
            }

            if (!empty($filtros['documento_id'])) {
                $conditions[] = "la.documento_id = :documento_id";
                $params[':documento_id'] = $filtros['documento_id'];
            }

            if (!empty($filtros['data_inicio'])) {
                $conditions[] = "la.criado_em >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
            }

            if (!empty($filtros['data_fim'])) {
                $conditions[] = "la.criado_em <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'] . ' 23:59:59';
            }

            if (!empty($filtros['acao'])) {
                $conditions[] = "la.acao LIKE :acao";
                $params[':acao'] = '%' . $filtros['acao'] . '%';
            }

            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }

            $sql .= " ORDER BY la.criado_em DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($sql);
            
            // Bind dos parâmetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Erro ao buscar logs de auditoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obter estatísticas de auditoria
     */
    public function obterEstatisticas($filtros = []) {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_logs,
                        COUNT(DISTINCT usuario_id) as usuarios_ativos,
                        COUNT(DISTINCT documento_id) as documentos_afetados,
                        COUNT(DISTINCT ip_address) as ips_unicos,
                        MIN(criado_em) as data_primeiro_log,
                        MAX(criado_em) as data_ultimo_log
                    FROM logs_auditoria 
                    WHERE 1=1";

            $params = [];
            $conditions = [];

            // Aplicar filtros
            if (!empty($filtros['data_inicio'])) {
                $conditions[] = "criado_em >= :data_inicio";
                $params[':data_inicio'] = $filtros['data_inicio'] . ' 00:00:00';
            }

            if (!empty($filtros['data_fim'])) {
                $conditions[] = "criado_em <= :data_fim";
                $params[':data_fim'] = $filtros['data_fim'] . ' 23:59:59';
            }

            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $estatisticas = $stmt->fetch();

            // Estatísticas por tipo
            $sql_tipos = "SELECT tipo, COUNT(*) as total 
                          FROM logs_auditoria 
                          WHERE 1=1";

            if (!empty($conditions)) {
                $sql_tipos .= " AND " . implode(" AND ", $conditions);
            }

            $sql_tipos .= " GROUP BY tipo ORDER BY total DESC";

            $stmt = $this->conn->prepare($sql_tipos);
            $stmt->execute($params);
            $estatisticas_por_tipo = $stmt->fetchAll();

            // Ações mais frequentes
            $sql_acoes = "SELECT acao, COUNT(*) as total 
                          FROM logs_auditoria 
                          WHERE 1=1";

            if (!empty($conditions)) {
                $sql_acoes .= " AND " . implode(" AND ", $conditions);
            }

            $sql_acoes .= " GROUP BY acao ORDER BY total DESC LIMIT 10";

            $stmt = $this->conn->prepare($sql_acoes);
            $stmt->execute($params);
            $acoes_frequentes = $stmt->fetchAll();

            return [
                'estatisticas_gerais' => $estatisticas,
                'estatisticas_por_tipo' => $estatisticas_por_tipo,
                'acoes_frequentes' => $acoes_frequentes
            ];

        } catch (Exception $e) {
            error_log("Erro ao obter estatísticas de auditoria: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Exportar logs para CSV
     */
    public function exportarCSV($filtros = []) {
        try {
            $logs = $this->buscarLogs($filtros, 10000, 0); // Limite alto para exportação
            
            if (empty($logs)) {
                return false;
            }

            // Criar arquivo CSV em memória
            $output = fopen('php://output', 'w');
            
            // Cabeçalho
            fputcsv($output, [
                'Data/Hora', 'Usuário', 'Ação', 'Tipo', 'Documento', 
                'Descrição', 'IP', 'User Agent'
            ], ';');

            // Dados
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['criado_em'],
                    $log['usuario_nome'] ?: 'Sistema',
                    $log['acao'],
                    $log['tipo'],
                    $log['documento_titulo'] ?: 'N/A',
                    $log['descricao'],
                    $log['ip_address'],
                    $log['user_agent']
                ], ';');
            }

            fclose($output);
            return true;

        } catch (Exception $e) {
            error_log("Erro ao exportar logs: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Limpar logs antigos (manutenção)
     */
    public function limparLogsAntigos($dias = 365) {
        try {
            $data_limite = date('Y-m-d H:i:s', strtotime("-$dias days"));
            
            $sql = "DELETE FROM logs_auditoria WHERE criado_em < :data_limite";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([':data_limite' => $data_limite]);

            $linhas_afetadas = $stmt->rowCount();
            
            // Registrar a própria ação de limpeza
            $this->registrar(
                'LIMPEZA_LOGS', 
                'sistema', 
                null, 
                null, 
                "Limpeza automática de logs antigos ($dias dias). $linhas_afetadas registros removidos."
            );

            return $linhas_afetadas;

        } catch (Exception $e) {
            error_log("Erro ao limpar logs antigos: " . $e->getMessage());
            return false;
        }
    }
}
?>