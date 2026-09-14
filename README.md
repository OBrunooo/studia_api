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

### 1. Clonar e instalar

```bash
git clone https://github.com/OBrunooo/studia-api.git
cd studia-api
composer run setup
```

O comando `composer run setup` executa:

- `composer install`
- Cria `.env` a partir de `.env.example` (se não existir)
- Gera a `APP_KEY`
- Roda migrations, instala dependências npm e build do frontend

### 2. Configurar o `.env`

Ajuste as variáveis de ambiente para desenvolvimento. Exemplo:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_PORT=8000

REDIS_HOST=redis
QUEUE_CONNECTION=redis

OPENAI_API_KEY=sk-...
```

### 3. Subir os containers

```bash
./vendor/bin/sail up -d
```

A API ficará disponível em **http://localhost:8000** (porta configurável via `APP_PORT` no `.env`).

O `compose.yaml` é auto-suficiente: sobe `laravel.test`, `redis` e `queue` em uma rede interna criada pelo próprio Compose. Não é preciso criar redes externas nem editar o arquivo — tudo que depende da infra da VPS (Traefik e as redes compartilhadas) vive no `compose.prod.yaml`.

---

## Deploy na VPS

O `compose.prod.yaml` é um overlay aplicado por cima do `compose.yaml`. Ele remove as portas publicadas em `localhost`, conecta os containers às redes externas `traefik` e `backend-net` e adiciona as labels de roteamento e TLS do Traefik.

```bash
docker compose -f compose.yaml -f compose.prod.yaml up -d --build
```

As redes externas precisam existir no host antes do primeiro deploy, já que são compartilhadas com os outros serviços da VPS:

```bash
docker network create traefik
docker network create backend-net
```

---

## Autor

**Bruno Gomes** · [GitHub](https://github.com/OBrunooo)

---

## Licença

MIT
