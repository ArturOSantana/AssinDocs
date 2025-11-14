// visualizar.php
<?php
// Implementar visualizador PDF com assinaturas
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $documento['titulo']; ?></h5>
                </div>
                <div class="card-body">
                    <iframe src="<?php echo $documento['arquivo_path']; ?>" 
                            width="100%" height="600px"></iframe>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6>Informações do Documento</h6>
                </div>
                <div class="card-body">
                    <p><strong>Hash:</strong> <code><?php echo $documento['hash_documento']; ?></code></p>
                    <p><strong>Status:</strong> <span class="badge bg-<?php echo $status_cor; ?>"><?php echo $documento['status']; ?></span></p>
                    <p><strong>Criado em:</strong> <?php echo $documento['criado_em']; ?></p>
                    
                    <h6>Signatários:</h6>
                    <ul>
                        <?php foreach($signatarios as $s): ?>
                        <li><?php echo $s['nome']; ?> (<?php echo $s['email']; ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <button class="btn btn-success btn-assinar" data-doc-id="<?php echo $documento['id']; ?>">
                        <i class="fas fa-signature"></i> Assinar Documento
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>