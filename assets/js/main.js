// Pequenas animações suaves e interações
document.addEventListener("DOMContentLoaded", () => {
  const heroText = document.querySelector(".hero h1");
  if (heroText) {
    heroText.style.opacity = "0";
    heroText.style.transform = "translateY(20px)";
    setTimeout(() => {
      heroText.style.transition = "all 1s ease";
      heroText.style.opacity = "1";
      heroText.style.transform = "translateY(0)";
    }, 300);
  }

  // Validação simples de formulários
  const forms = document.querySelectorAll('form');
  forms.forEach(form => {
    form.addEventListener('submit', (e) => {
      const inputs = form.querySelectorAll('input[required]');
      let valid = true;
      inputs.forEach(input => {
        if (!input.value.trim()) {
          valid = false;
          input.style.border = '1px solid red';
        } else {
          input.style.border = 'none';
        }
      });
      if (!valid) {
        e.preventDefault();
        alert('Preencha todos os campos obrigatórios.');
      }
    });
  });
});
document.addEventListener('DOMContentLoaded', function() {
            // Máscara para CPF
            const cpfInput = document.querySelector('.cpf-mask');
            if (cpfInput) {
                cpfInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.length <= 11) {
                        value = value.replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d)/, '$1.$2')
                                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                    }
                    e.target.value = value;
                });
            }

            // Validação do formulário
            const form = document.getElementById('formAssinatura');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const fileInput = form.querySelector('input[type="file"]');
                    const termos = form.querySelector('#termos');
                    
                    if (fileInput.files[0] && fileInput.files[0].size > 2 * 1024 * 1024) {
                        e.preventDefault();
                        alert('Arquivo muito grande! O tamanho máximo é 2MB.');
                        return;
                    }
                    
                    if (!termos.checked) {
                        e.preventDefault();
                        alert('Você deve aceitar os termos e condições para assinar.');
                        termos.focus();
                        return;
                    }
                });
            }
        });

            function previewPDF(input) {
        const preview = document.getElementById('pdf-preview');
        const iframe = document.getElementById('pdf-iframe');

        if (input.files && input.files[0]) {
            const file = input.files[0];

            // Validação de tamanho
            if (file.size > 5 * 1024 * 1024) {
                alert('Arquivo muito grande! O tamanho máximo é 5MB.');
                input.value = '';
                preview.style.display = 'none';
                return;
            }

            // Validação de tipo
            if (file.type !== 'application/pdf') {
                alert('Apenas arquivos PDF são permitidos!');
                input.value = '';
                preview.style.display = 'none';
                return;
            }

            // Mostrar preview
            const url = URL.createObjectURL(file);
            iframe.src = url;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }

    function addSignatario() {
        const container = document.getElementById('signatarios-container');
        const newInput = document.createElement('input');
        newInput.type = 'email';
        newInput.name = 'signatarios[]';
        newInput.className = 'form-control mb-2';
        newInput.placeholder = 'E-mail adicional do signatário';
        container.appendChild(newInput);
    }