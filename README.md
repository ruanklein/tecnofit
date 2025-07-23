# Tecnofit Movement Ranking API

API RESTful desenvolvida em PHP puro para consultar rankings de movimentos com base em recordes pessoais de usuários.

## Pré-requisitos

- Docker
- Docker Compose
- Git

## Instalação

1. Clone o repositório:

```bash
git clone <url-do-repositorio>
cd tecnofit
```

2. Execute o Docker Compose:

```bash
docker compose up app # para subir a aplicação
docker compose up seeds # para inserir dados de exemplo
docker compose up test # para executar os testes
```

## Como usar

### Verificar se a API está funcionando

```bash
curl http://localhost:8000/
```

Resposta esperada:

```json
{
  "status": "OK",
  "message": "Tecnofit Movement Ranking API",
  "timestamp": "2024-01-01 12:00:00",
  "php_version": "8.4.10"
}
```

### Consultar ranking por ID do movimento

```bash
curl http://localhost:8000/api/movements/1/ranking
```

### Consultar ranking por nome do movimento

```bash
curl http://localhost:8000/api/movements/Deadlift/ranking
```

### Resposta da API

```json
{
  "movement": "Deadlift",
  "ranking": [
    {
      "position": 1,
      "user_name": "Jose",
      "personal_record": 190,
      "record_date": "2021-01-06 00:00:00"
    },
    {
      "position": 2,
      "user_name": "Joao",
      "personal_record": 180,
      "record_date": "2021-01-02 00:00:00"
    },
    {
      "position": 3,
      "user_name": "Paulo",
      "personal_record": 170,
      "record_date": "2021-01-01 00:00:00"
    }
  ]
}
```

## Endpoints

| Método | Endpoint                        | Descrição                     |
| ------ | ------------------------------- | ----------------------------- |
| GET    | `/`                             | Health check da API           |
| GET    | `/health`                       | Health check da API           |
| GET    | `/api/movements/{id}/ranking`   | Ranking do movimento por ID   |
| GET    | `/api/movements/{nome}/ranking` | Ranking do movimento por nome |

### Parâmetros

- `{id}`: ID numérico do movimento (ex: 1, 2)
- `{nome}`: Nome do movimento (ex: Deadlift, "Back Squat")

### Códigos de resposta

- `200`: Sucesso
- `404`: Movimento não encontrado
- `500`: Erro interno do servidor

## Estrutura do Projeto

```
tecnofit/
├── src/
│   ├── Config/
│   │   └── Database.php          # Configuração e conexão com banco
│   ├── Controllers/
│   │   └── MovementController.php # Controller dos endpoints
│   ├── Services/
│   │   └── RankingService.php    # Lógica de negócio do ranking
├── database/
│   ├── Migrations/               # Scripts de criação das tabelas
│   ├── Seeds/                    # Dados de exemplo
│   └── seeds.php                 # Script para inserir dados
├── tests/
│   ├── Unit/                     # Testes unitários
│   └── bootstrap.php             # Configuração dos testes
├── index.php                     # Ponto de entrada da API
├── compose.yml                   # Configuração do Docker
├── Dockerfile                    # Imagem personalizada do PHP
└── composer.json                 # Dependências do PHP
```

## Banco de Dados

### Tabelas

- `movements`: Movimentos disponíveis
- `users`: Usuários do sistema
- `personal_records`: Recordes pessoais dos usuários

### Dados de exemplo

O projeto inclui dados de exemplo com:

- 3 usuários: Joao, Jose, Paulo
- 2 movimentos: Deadlift, Back Squat
- Múltiplos recordes pessoais para cada usuário

## Regras de Negócio

1. O ranking é ordenado pelo maior valor (decrescente)
2. Usuários com o mesmo recorde pessoal compartilham a mesma posição
3. Apenas o maior valor de cada usuário é considerado (recorde pessoal)
4. Em caso de empate no valor, a data mais recente é considerada

## Tecnologias Utilizadas

- PHP 8.4
- MySQL 8.0
- Apache 2.4
- Docker & Docker Compose
- PHPUnit (testes)
- Composer (gerenciamento de dependências)
