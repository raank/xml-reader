# RaankReader

> Serviço simples de autenticação e leitura e inserção de arquivos em *XML*;

 **Usado as seguintes stacks**:
- PHP 8.0
    - Arrow Functions
    - Constructor Properties Promotion
- [Lumen 8.x](https://lumen.laravel.com/)
- Docker
- MySQL
- Tests (PHPUnit)
- [Swagger](https://zircote.github.io/swagger-php) (Documentação) 
- [PHP MD](https://phpmd.org) (Mess Dectector)
- [PHP STAN](https://github.com/phpstan/phpstan) (Static Analysis Tool)
---

Install:
```shell
make install
```

> **OBS**: Esse comando vai disponibilizar um token, onde você poderá inserir no cabeçalhos das requisições que necessitam de segurança entre applicações.

![Instalação com Token](/storage/app/file-make-install.jpeg?raw=true "Instalação com Token")

Copie e cole o token `X-App-Token` e cole no atributo passado no cabeçalho da requisição.

Ao finalizar a instalação, configure as variáveis de ambiente para disparo de e-mails.

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME={SEU USERNAME}
MAIL_PASSWORD={SUA SENHA}
MAIL_ENCRYPTION=tls
```

Rodando Testes:
```shell
make test
```

![Cobertura de Testes](/storage/app/file-make-test.jpeg?raw=true "Cobertura de Testes")

Confira a documentação de API: [Swagger API](https://bit.ly/2U6CRBU)