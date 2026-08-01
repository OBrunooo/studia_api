# Studia API

API Laravel do projeto Studia, com autenticação via Sanctum, filas Redis e deploy em produção atrás do Traefik.

## Requisitos

- Docker e Docker Compose
- [Laravel Sail](https://laravel.com/docs/sail) (incluído como dependência de desenvolvimento)
- PHP 8.2+ e Composer (para instalação inicial fora do container)

## Serviços Docker

| Serviço | Descrição |
|---------|-----------|
| `laravel.test` | Aplicação Laravel (PHP 8.5) |
| `redis` | Cache e filas |
| `queue` | Worker `php artisan queue:work redis` |

## Desenvolvimento local

### 1. Clonar e instalar

```bash
git clone <url-do-repositorio>
cd API_projeto_estudos
composer run setup
```

O comando `composer run setup` executa:

- `composer install`
- Cria `.env` a partir de `.env.example` (se não existir)
- Cria `compose.override.yaml` a partir de `compose.override.yaml.example` (se não existir)
- Gera a `APP_KEY`
- Roda migrations, instala dependências npm e build do frontend

### 2. Configurar o `.env`

Ajuste as variáveis de ambiente para desenvolvimento. Exemplo:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_PORT=8000
```

### 3. Criar a rede Traefik (apenas na primeira vez)

Em desenvolvimento local não há um container Traefik real, mas o `compose.yaml` referencia a rede externa `traefik`. Crie-a uma vez:

```bash
docker network create traefik
```

### 4. Subir os containers

```bash
./vendor/bin/sail up -d
```

A API ficará disponível em **http://localhost:8000** (porta configurável via `APP_PORT` no `.env`).

### Comandos úteis

```bash
./vendor/bin/sail artisan migrate          # Rodar migrations
./vendor/bin/sail artisan queue:work       # Worker manual (já há um serviço queue)
./vendor/bin/sail logs -f laravel.test     # Logs da aplicação
./vendor/bin/sail down                     # Parar containers
```

## Docker Compose: dev vs produção

O projeto usa dois arquivos de compose com papéis distintos:

| Arquivo | Versionado | Uso |
|---------|------------|-----|
| `compose.yaml` | Sim | Base compartilhada. Em produção, é o único arquivo usado. |
| `compose.override.yaml.example` | Sim | Template para desenvolvimento local. |
| `compose.override.yaml` | Não (`.gitignore`) | Sobrescreve o compose base **apenas em dev**, expondo a porta local. |

### Desenvolvimento

O Docker Compose mescla automaticamente `compose.yaml` + `compose.override.yaml`. O override publica a porta da aplicação somente em `127.0.0.1`:

```yaml
ports:
  - '127.0.0.1:${APP_PORT:-8000}:80'
```

Isso permite acessar a API diretamente no localhost, sem depender do Traefik.

Se o `compose.override.yaml` não existir após o clone, crie-o manualmente:

```bash
cp compose.override.yaml.example compose.override.yaml
```

### Produção

No servidor (VPS), **não** deve existir `compose.override.yaml`. Apenas o `compose.yaml` é usado:

- Sem `ports` publicados no host
- Acesso externo exclusivamente via **Traefik** (HTTPS)
- Domínio: `studia.vps-bruno-gomes.com`
- Rede Docker `traefik` deve já existir no servidor

Configure o `.env` de produção com as credenciais e URLs corretas antes de subir:

```bash
./vendor/bin/sail up -d
```

## Estrutura de filas

Jobs assíncronos (ex.: geração de questões por tema) são processados pelo serviço `queue`, que executa:

```bash
php artisan queue:work redis --sleep=3 --tries=1 --timeout=1800
```

A conexão de fila padrão é `redis` (`QUEUE_CONNECTION=redis` no `.env`).

## Licença

MIT
# StudIA API

> API de estudos que gera questões de múltipla escolha sobre qualquer tema usando IA.

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)](https://php.net)
[![Redis](https://img.shields.io/badge/Redis-DC382D?logo=redis&logoColor=white)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-2496ED?logo=docker&logoColor=white)](https://docker.com)
[![OpenAI](https://img.shields.io/badge/OpenAI-412991?logo=openai&logoColor=white)](https://openai.com)

**Produção:** `https://studia.vps-bruno-gomes.com/api`

---

## O que é

O StudIA é uma plataforma de estudos onde o usuário digita um tema — como "Revolução Francesa" ou "Laravel" — e recebe uma trilha de **7 módulos com questões de múltipla escolha**, do básico ao avançado.

Se o tema ainda não existe, a API **cria tudo do zero com IA**. Se já existe, o usuário é vinculado na hora.

---

## Como funciona (a grosso modo)

1. O usuário pede um tema pela API
2. A IA valida se o tema faz sentido (ex: "cadeira" → rejeitado)
3. Se for válido, um job em background gera as questões via OpenAI
4. A IA cria muitas perguntas, seleciona as melhores, monta as alternativas e revisa a qualidade
5. Tudo é salvo em 7 módulos de 7 questões cada
6. O usuário recebe uma notificação quando a trilha fica pronta
7. Ele estuda módulo a módulo e acompanha o progresso em porcentagem

A geração demora alguns minutos, por isso roda em **fila assíncrona** — a API responde na hora e o trabalho pesado fica por conta do worker.

---

## Stack

- **Laravel 12** + **PHP 8.5**
- **MariaDB** — banco de dados
- **Redis** — filas e cache
- **OpenAI** — geração de questões
- **Docker Compose** + **Traefik** — deploy com HTTPS

---

## Rodando localmente

```bash
git clone git@github.com:OBrunooo/studia-api.git
cd studia-api
composer run setup

# Configure o .env (banco, Redis, OPENAI_API_KEY)
docker network create traefik   # só na primeira vez
./vendor/bin/sail up -d
```

API disponível em **http://localhost:8000**

---

## Autor

**Bruno Gomes** · [GitHub](https://github.com/OBrunooo)

---

## Licença

MIT
