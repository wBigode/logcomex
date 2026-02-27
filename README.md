# Logcomex - Pokédex

Sistema de gerenciamento de Pokémon com sincronização assíncrona via PokeAPI.

## Stack

- **Backend:** PHP 8.4, Laravel 12, Laravel Breeze
- **Frontend:** Vue 3 (Composition API), Inertia.js, Tailwind CSS, Vite 7
- **Banco de dados:** MySQL 8.4
- **Fila:** Redis (Alpine)
- **Busca:** Meilisearch
- **Storage:** RustFS (S3-compatível)
- **Containerização:** Docker via Laravel Sail

## Requisitos

- Docker e Docker Compose
- Usuário no grupo `docker` (ou usar `sg docker -c "..."`)

## Instalação

1. Clone o repositório:

```bash
git clone <url-do-repositorio> logcomex
cd logcomex
```

2. Copie o arquivo de ambiente:

```bash
cp .env.example .env
```

3. Instale as dependências e configure o projeto:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

4. Suba os containers:

```bash
./vendor/bin/sail up -d
```

5. Gere a chave da aplicação:

```bash
./vendor/bin/sail artisan key:generate
```

6. Execute as migrations:

```bash
./vendor/bin/sail artisan migrate
```

7. Instale as dependências do frontend:

```bash
./vendor/bin/sail npm install
```

8. Inicie o servidor de desenvolvimento do Vite:

```bash
./vendor/bin/sail npm run dev
```

A aplicação estará disponível em `http://localhost`.

## Filas (Queue)

O sistema utiliza **Redis** como driver de filas para processar jobs assíncronos (ex: sincronização de Pokémon via PokeAPI).

Para iniciar o worker de filas:

```bash
./vendor/bin/sail artisan queue:work
```

## Funcionalidades

### Autenticação

- Registro, login e recuperação de senha via Laravel Breeze
- Gerenciamento de perfil (editar dados, alterar senha, excluir conta)

### CRUD de Pokémon

Acessível via menu **Pokémon**.

- **Listagem** paginada com busca por nome e tipo
- **Criar** novo Pokémon (nome, tipo, altura em cm, peso em kg, URL do sprite, status ativo/inativo)
- **Editar** dados de um Pokémon existente
- **Excluir** via soft delete (desativa o registro pela coluna `ativo`)

### Sincronização com PokeAPI

Acessível via menu **Pokémon Sync**.

- Importa toda a base de Pokémon da [PokeAPI](https://pokeapi.co) de forma assíncrona via Job
- Dados importados: nome, tipo primário, altura (convertida para cm), peso (convertido para kg), sprite e ID externo
- Utiliza `updateOrCreate` pelo `id_externo` para evitar duplicatas
- Paginação automática pela API (100 por página) com delay de 100ms entre requisições para não sobrecarregar o servidor da PokeAPI
- **Proteção contra execuções paralelas:** apenas uma sincronização pode estar ativa por vez
- **Acompanhamento em tempo real:** o frontend faz polling a cada 2 segundos exibindo o progresso (total de Pokémon importados)
- **Persistência de estado:** ao navegar para outra página e voltar, o progresso da sincronização ativa é restaurado automaticamente
- Alertas visuais: azul (em andamento), verde (concluído), vermelho (falha)

## Estrutura do Projeto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── PokemonController.php          # CRUD de Pokémon
│   │   └── PokemonSyncJobController.php   # Sincronização com PokeAPI
│   └── Requests/
│       ├── StorePokemonRequest.php         # Validação de criação
│       └── UpdatePokemonRequest.php        # Validação de edição
├── Jobs/
│   └── PokemonSyncJob.php                 # Job assíncrono de sync
├── Models/
│   ├── Pokemon.php                        # Model Pokémon
│   └── PokemonSyncJob.php                 # Model de rastreamento do job
└── Services/
    ├── PokemonService.php                 # Regras de negócio do CRUD
    └── PokemonSyncService.php             # Regras de negócio da sync

resources/js/
├── Pages/
│   ├── Pokemon/
│   │   ├── Index.vue                      # Listagem com busca
│   │   ├── Create.vue                     # Formulário de criação
│   │   ├── Edit.vue                       # Formulário de edição
│   │   └── Partials/
│   │       └── PokemonForm.vue            # Componente de formulário compartilhado
│   └── PokemonSync/
│       └── Index.vue                      # Painel de sincronização
└── Layouts/
    └── AuthenticatedLayout.vue            # Layout com navegação
```

## Arquitetura

- **Service Layer:** regras de negócio isoladas em Services, controllers delegam operações
- **FormRequests:** validação e autorização extraídas dos controllers
- **Componentes compartilhados:** `PokemonForm.vue` reutilizado entre Create e Edit (DRY)
- **Soft Delete:** exclusão lógica via coluna `ativo` ao invés de remoção física

## Banco de Dados

### Tabela `pokemon`

| Coluna      | Tipo         | Descrição                              |
|-------------|--------------|----------------------------------------|
| id          | bigint (PK)  | Identificador interno                  |
| nome        | varchar(255) | Nome do Pokémon                        |
| tipo        | varchar(255) | Tipo primário                          |
| altura      | integer      | Altura em centímetros                  |
| peso        | decimal(8,2) | Peso em quilogramas                    |
| sprite      | varchar(255) | URL da imagem                          |
| id_externo  | integer null | ID da PokeAPI (nulo se criado via CRUD)|
| ativo       | tinyint      | 1 = ativo, 0 = inativo                |
| created_at  | timestampTz  | Data de criação                        |
| updated_at  | timestampTz  | Data de atualização                    |

### Tabela `pokemon_sync_jobs`

| Coluna                 | Tipo         | Descrição                              |
|------------------------|--------------|----------------------------------------|
| id                     | bigint (PK)  | Identificador do job                   |
| status                 | varchar(50)  | pending, processing, completed, failed |
| total_pokemon_imported | integer      | Contador de Pokémon importados         |
| started_at             | timestamp null | Início do processamento              |
| finished_at            | timestamp null | Fim do processamento                 |
| ativo                  | tinyint      | 1 = ativo, 0 = inativo                |
| created_at             | timestampTz  | Data de criação                        |
| updated_at             | timestampTz  | Data de atualização                    |

## Scripts Úteis

```bash
# Subir os containers
./vendor/bin/sail up -d

# Parar os containers
./vendor/bin/sail down

# Executar migrations
./vendor/bin/sail artisan migrate

# Rollback das migrations
./vendor/bin/sail artisan migrate:rollback

# Recriar todas as tabelas
./vendor/bin/sail artisan migrate:fresh

# Worker de filas
./vendor/bin/sail artisan queue:work

# Build de produção do frontend
./vendor/bin/sail npm run build

# Limpar caches
./vendor/bin/sail artisan optimize:clear
```
