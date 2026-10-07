# Hotel API

API REST para gerenciamento de operações hoteleiras. O projeto desenvolvido como solução para o desafio técnico permite importar dados XML, administrar quartos, criar reservas, controlar disponibilidade, aplicar cupons e taxas, registrar pagamentos e gerenciar usuários por hotel.

A aplicação utiliza Laravel, MySQL, Laravel Sail/Docker, Laravel Sanctum, PHPUnit e Swagger/OpenAPI 3.0.

## Funcionalidades

### Requisitos principais

- Importação de hotéis, quartos, hóspedes, reservas, diárias e formas de pagamento a partir de arquivos XML.
- Modelagem de banco de dados versionada.
- Comando Artisan para importação de XML, compatível com execução agendada via CRON.
- CRUD REST de quartos.
- Criação de reservas via API REST.
- Respostas da API em JSON.

### Diferenciais implementados

- Documentação interativa com Swagger/OpenAPI 3.0.
- Testes automatizados com PHPUnit.
- Docker com Laravel Sail.
- Autenticação via Laravel Sanctum.
- Controle de acesso e permissões por usuário do hotel.
- Gestão de usuários por hotel.
- Gestão de pagamentos por hotel.
- Cupons de desconto.
- Taxas adicionais.
- Verificação de disponibilidade de quartos por período.
- Logs de aplicação.
- Versionamento por Git e organização de branches/PRs.

## Tecnologias

- PHP 8+
- Laravel
- MySQL
- Laravel Sail / Docker Compose
- Laravel Sanctum
- PHPUnit
- L5-Swagger
- OpenAPI 3.0

## Estrutura relevante

```text
app/
├── Console/Commands/ImportXmlData.php   # Importação de XML
├── Http/
│   ├── Controllers/                     # Controllers REST e Swagger
│   ├── Requests/                        # Validações das requisições
│   ├── Resources/                       # Formatação das respostas JSON
│   └── Middleware/                      # Autenticação e autorização
├── Models/                              # Models Eloquent
├── OpenApi/OpenApi.php                  # Metadados e schemas OpenAPI
└── Services/                            # Regras de negócio

database/
├── migrations/                          # Versionamento do banco
└── xml/                                 # Arquivos XML de referência, quando aplicável

docs/
├── database/
│   ├── hotel-api.mwb                    # Modelo MySQL Workbench
│   └── schema.sql                       # Script SQL da modelagem
└── xml/
    ├── hotels.xml
    ├── rooms.xml
    └── reserves.xml

routes/
└── api.php                              # Rotas da API
```

## Pré-requisitos

Para executar o projeto com Docker, instale:

- Docker
- Docker Compose

Verifique a instalação:

```bash
docker --version
docker compose version
```

Também é recomendado ter Git instalado:

```bash
git --version
```

## Instalação

Clone o repositório:

```bash
git clone [https://github.com/SavioKSLopes/hotel-api.git](https://github.com/SavioKSLopes/hotel-api.git)
cd hotel-api
```

Instale as dependências PHP:

```bash
composer install
```

Gere a chave da aplicação:

```bash
php artisan key:generate
```

Caso utilize Laravel Sail, é possível executar os comandos pelo container:

```bash
./vendor/bin/sail artisan key:generate
```

## Executando com Docker/Sail

Suba os containers em segundo plano:

```bash
./vendor/bin/sail up -d
```

Confira o estado dos serviços:

```bash
./vendor/bin/sail ps
```

A aplicação fica disponível em:

```text
http://localhost:8080
```

Para acompanhar logs:

```bash
./vendor/bin/sail logs -f
```

Para encerrar os containers:

```bash
./vendor/bin/sail down
```

## Banco de dados

Com os containers em execução, execute as migrations:

```bash
./vendor/bin/sail artisan migrate
```

Para recriar o banco do zero:

```bash
./vendor/bin/sail artisan migrate:fresh
```

Para recriar o banco e executar seeders

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

A modelagem gerada com MySql WorkBench também está disponível em:

```text
docs/database/schema.sql
```

O arquivo `.mwb` pode ser aberto no MySQL Workbench.

## Importação de XML

O projeto possui um comando Artisan responsável por importar os dados XML e persistir as informações no banco de dados.

Os XMLs de referência estão em:

```text
docs/xml/hotels.xml
docs/xml/rooms.xml
docs/xml/reserves.xml
```

Liste os comandos de importação disponíveis:

```bash
./vendor/bin/sail artisan list | grep -i import
```

Execute o comando de importação com a assinatura exibida pelo comando acima. Exemplo:

```bash
./vendor/bin/sail artisan import:xml
```

Caso o comando aceite argumentos ou opções, consulte a ajuda:

```bash
./vendor/bin/sail artisan help import:xml
```

> Ajuste `import:xml` para a assinatura real registrada em `ImportXmlData.php`.

## Execução via CRON

Para desenvolvimento, é possível manter o scheduler do Laravel em execução:

```bash
./vendor/bin/sail artisan schedule:work
```

Em produção, configure o CRON do servidor para chamar o scheduler a cada minuto:

```cron
* * * * * cd /caminho/para/hotel-api && ./vendor/bin/sail artisan schedule:run >> /dev/null 2>&1
```

Se o ambiente de produção não usar Sail, use:

```cron
* * * * * cd /caminho/para/hotel-api && php artisan schedule:run >> /dev/null 2>&1
```

O agendamento pode chamar o comando de importação XML conforme a regra configurada na aplicação.

## Documentação Swagger/OpenAPI

A documentação interativa da API é gerada com L5-Swagger e OpenAPI 3.0.

Gere ou atualize o arquivo de documentação:

```bash
./vendor/bin/sail artisan l5-swagger:generate
```

Acesse a interface Swagger:

```text
http://localhost:8080/api/documentation
```

A documentação apresenta endpoints e exemplos para:

- Quartos.
- Reservas.
- Pagamentos.
- Usuários de hotéis.
- Autenticação Bearer/Sanctum, quando exigida pela rota.

## Autenticação

As rotas protegidas utilizam autenticação por token Bearer com Laravel Sanctum.

No Swagger, clique em **Authorize** e informe o token no formato:

```text
Bearer SEU_TOKEN
```

Exemplo de cabeçalho HTTP:

```http
Authorization: Bearer SEU_TOKEN
Accept: application/json
```

## Rotas principais

A lista completa e atualizada de rotas pode ser consultada com:

```bash
./vendor/bin/sail artisan route:list --path=api
```

### Quartos

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/rooms` | Lista quartos com paginação |
| POST | `/api/rooms` | Cria um quarto |
| GET | `/api/rooms/{room}` | Exibe um quarto |
| PUT | `/api/rooms/{room}` | Atualiza completamente um quarto |
| PATCH | `/api/rooms/{room}` | Atualiza parcialmente um quarto |
| DELETE | `/api/rooms/{room}` | Remove um quarto |

Exemplo de criação de quarto:

```http
POST /api/rooms
Authorization: Bearer SEU_TOKEN
Content-Type: application/json
Accept: application/json
```

```json
{
  "hotel_id": 1,
  "external_id": "ROOM-101",
  "name": "Quarto Standard 101"
}
```

### Reservas

| Método | Endpoint | Descrição |
|---|---|---|
| POST | `/api/reserves` | Cria uma reserva |

Exemplo de criação de reserva:

```http
POST /api/reserves
Content-Type: application/json
Accept: application/json
```

```json
{
  "external_id": "RES-0001",
  "hotel_id": 1,
  "room_id": 1,
  "guest_id": 1,
  "check_in": "2026-10-20",
  "check_out": "2026-10-23",
  "total": 600.00,
  "coupon_code": "PROMO10"
}
```

Campos obrigatórios:

- `external_id`
- `hotel_id`
- `room_id`
- `guest_id`
- `check_in`
- `check_out`
- `total`

O campo `coupon_code` é opcional. A data de `check_out` deve ser posterior à data de `check_in`.

### Pagamentos

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/hotels/{hotelId}/payments` | Lista pagamentos do hotel |
| POST | `/api/hotels/{hotelId}/payments` | Cria um pagamento |
| GET | `/api/hotels/{hotelId}/payments/{paymentId}` | Exibe um pagamento |
| PUT | `/api/hotels/{hotelId}/payments/{paymentId}` | Atualiza um pagamento |
| PATCH | `/api/hotels/{hotelId}/payments/{paymentId}` | Atualiza parcialmente um pagamento |

Exemplo de criação de pagamento:

```json
{
  "reserve_id": 1,
  "payment_method_id": 1,
  "value": 250.00,
  "status": "pending",
  "external_reference": "TXN-123456",
  "metadata": {
    "gateway": "stripe",
    "transaction_id": "pi_123456"
  },
  "paid_at": "2026-10-06T22:30:00Z"
}
```

Os status permitidos para atualização são:

```text
pending
paid
refunded
failed
```

### Usuários do hotel

| Método | Endpoint | Descrição |
|---|---|---|
| GET | `/api/hotels/{hotelId}/users` | Lista usuários do hotel |
| POST | `/api/hotels/{hotelId}/users` | Cria usuário no hotel |
| GET | `/api/hotels/{hotelId}/users/{userId}` | Exibe usuário do hotel |
| PUT | `/api/hotels/{hotelId}/users/{userId}` | Atualiza usuário |
| PATCH | `/api/hotels/{hotelId}/users/{userId}` | Atualiza parcialmente usuário |
| DELETE | `/api/hotels/{hotelId}/users/{userId}` | Remove usuário |

Exemplo de criação de usuário:

```json
{
  "name": "Maria Souza",
  "email": "maria.souza@hotel.com",
  "password": "senha-segura-123",
  "role": "manager",
  "is_active": true
}
```

Funções permitidas:

```text
owner
manager
receptionist
```

## Respostas e erros

A API responde em JSON.

Códigos HTTP usados com frequência:

| Código | Significado |
|---:|---|
| 200 | Requisição processada com sucesso |
| 201 | Recurso criado com sucesso |
| 204 | Recurso removido com sucesso, sem corpo de resposta |
| 401 | Token ausente, inválido ou usuário não autenticado |
| 403 | Usuário autenticado sem permissão para a operação |
| 404 | Recurso não encontrado |
| 422 | Falha de validação ou regra de negócio |

Exemplo de erro de validação:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "check_out": [
      "The check out field must be a date after check in."
    ]
  }
}
```

## Testes

Os testes de feature dependem do MySQL definido pela rede Docker. Portanto, execute-os dentro do Sail:

```bash
./vendor/bin/sail artisan test
```

Para executar apenas uma classe de teste:

```bash
./vendor/bin/sail artisan test --filter=RoomApiTest
```

Para executar apenas um método:

```bash
./vendor/bin/sail artisan test --filter="it creates a room"
```

> Não execute `php artisan test` diretamente no host caso o `.env` use `DB_HOST=mysql`, pois esse hostname é resolvido dentro da rede Docker.

## Qualidade e manutenção

Antes de enviar alterações:

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail artisan l5-swagger:generate
git status
```

Para verificar as rotas:

```bash
./vendor/bin/sail artisan route:list --path=api
```

## Entrega

O repositório contém:

- Código-fonte Laravel.
- Migrations e modelagem de banco.
- Script SQL e arquivo MySQL Workbench.
- XMLs de referência.
- Comando de importação XML.
- Testes automatizados.
- Configuração Docker/Sail.
- Documentação Swagger/OpenAPI.
- README com instruções de execução.

## Autor

Desenvolvido por [Sávio Lopes](https://github.com/SavioKSLopes).
