# MedCare Digital

Sistema distribuído para organização da jornada pessoal de saúde, desenvolvido com **Laravel 11**, **Vue 3** e **Inertia.js**.

---

# 1. Resumo

O **MedCare Digital** é uma plataforma desenvolvida para centralizar e organizar informações pessoais de saúde em um único ambiente. O sistema permite ao usuário armazenar exames, receitas, vacinas, histórico clínico, lembretes e registros da sua jornada de saúde, facilitando o acesso às informações quando necessário.

Além da interface web, o sistema possui um assistente conversacional que simula a integração com o WhatsApp. Por meio dele, mensagens e documentos enviados pelo usuário são interpretados por Inteligência Artificial, que identifica o conteúdo e o registra automaticamente no módulo correspondente.

O principal objetivo é reduzir a desorganização das informações de saúde e proporcionar uma experiência simples, intuitiva e centralizada.

---

# 2. Arquitetura do Sistema

O MedCare Digital foi desenvolvido utilizando uma arquitetura modular baseada em Laravel e Vue, permitindo escalabilidade e facilidade de manutenção.

## Arquitetura Geral

```text
                   Usuário
                      │
        ┌─────────────┴─────────────┐
        │                           │
 Interface Web               Assistente (Chat)
     Vue 3                         IA
        │                           │
        └─────────────┬─────────────┘
                      │
                 Inertia.js
                      │
                 Laravel 11
                      │
        ┌─────────────┴─────────────┐
        │                           │
     Banco de Dados             Gemini API
(SQLite / MySQL)      (Classificação Inteligente)
```

## Componentes

### Front-end

- Desenvolvido em **Vue 3**
- Interface reativa
- Comunicação com Laravel através do Inertia.js

### Back-end

- Laravel 11
- Regras de negócio
- Autenticação
- Validações
- Controle dos módulos

### Banco de Dados

- SQLite para desenvolvimento
- MySQL para produção

### Inteligência Artificial

Utiliza a **Gemini API** para:

- interpretar mensagens;
- classificar documentos;
- identificar o módulo correto;
- auxiliar na organização das informações.

### Simulador Conversacional

Representa a futura integração oficial com o WhatsApp, permitindo registrar informações através de mensagens.

---

# 3. Tecnologias Utilizadas

| Tecnologia | Finalidade |
|------------|------------|
| Laravel 11 | Back-end e regras de negócio |
| PHP 8.1+ | Linguagem do servidor |
| Vue 3 | Interface do usuário |
| Inertia.js | Comunicação Front-end ↔ Back-end |
| Jetstream | Autenticação |
| PrimeVue | Componentes visuais |
| Tailwind CSS | Estilização |
| Vite | Build do Front-end |
| SQLite | Banco para desenvolvimento |
| MySQL 8 | Banco para produção |
| Gemini API | Inteligência Artificial |
| Git | Controle de versão |
| GitHub | Hospedagem do código |

---

# 4. Funcionamento dos Componentes

## Perfil de Saúde

Centraliza informações importantes do usuário como:

- tipo sanguíneo;
- alergias;
- medicamentos;
- doenças;
- condições clínicas.

---

## Exames

Permite cadastrar, visualizar e organizar exames juntamente com seus documentos.

---

## Receitas

Armazena receitas médicas e informações dos medicamentos prescritos.

---

## Vacinas

Organiza doses, datas e comprovantes de vacinação.

---

## Histórico Clínico

Armazena consultas, ocorrências e registros clínicos do usuário.

---

## Lembretes

Permite cadastrar:

- consultas;
- medicamentos;
- exames;
- vacinas;
- retornos.

---

## Central de Pendências

Exibe tudo aquilo que precisa de atenção em um único painel.

---

## Assistente MedCare

Recebe mensagens e documentos enviados pelo usuário.

A Inteligência Artificial identifica automaticamente:

- exames;
- receitas;
- vacinas;
- lembretes;
- histórico clínico.

Cada informação é enviada automaticamente para seu módulo correspondente.

---

## Jornada Inteligente

Consolida as informações cadastradas pelo usuário e gera um resumo organizado da jornada de saúde.

---

## Resumo em PDF

Permite gerar um documento contendo todas as principais informações para impressão ou apresentação durante consultas.

---

# 5. Como Executar o Sistema

## Requisitos

- PHP 8.1+
- Composer 2.x
- Node.js 18+
- NPM 8+
- MySQL 8 ou SQLite
- Git

---

## Clonar o projeto

```bash
git clone https://github.semsa/base-projects/template-laravel11-sakai-vue.git
```

---

## Instalar dependências

### PHP

```bash
composer update
```

### JavaScript

```bash
npm install
```

---

## Configuração

Criar o arquivo `.env`

```bash
cp .env.example .env
```

Gerar a chave da aplicação

```bash
php artisan key:generate
```

Criar o link simbólico do Storage

```bash
php artisan storage:link
```

Configurar o banco de dados no arquivo `.env`.

Exemplo usando SQLite:

```env
DB_CONNECTION=sqlite
```

Ou configurar:

```env
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Executar as migrações

```bash
php artisan migrate
```

---

## Executar o Projeto

Iniciar o servidor Laravel

```bash
php artisan serve
```

Iniciar o Vite

```bash
npm run dev
```

Abrir no navegador

```
http://127.0.0.1:8000
```

---

## 5.1. Organização do Projeto

## Front-end

```
resources/
└── js/
    ├── Pages/
    │   ├── PerfilSaude/
    │   ├── Exames/
    │   ├── Receitas/
    │   ├── Vacinas/
    │   ├── Historico/
    │   └── Lembretes/
    │
    ├── Components/
    │
    └── Assets/
```

Cada módulo possui:

- Index.vue
- Create.vue
- Edit.vue

---

## Back-end

```
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
│
├── Models/
│
└── Policies/
```

---

## Rotas

O projeto segue o padrão **Resource Controllers** do Laravel.

Exemplo:

```php
Route::resource('exames', ExameController::class);
```

---

## 5.2. Fluxo de Funcionamento

```text
Usuário
    │
    ▼
Cadastro/Login
    │
    ▼
Escolha do módulo
    │
    ├── Exames
    ├── Receitas
    ├── Vacinas
    ├── Histórico Clínico
    ├── Lembretes
    └── Perfil de Saúde
           │
           ▼
Armazenamento no Banco
           │
           ▼
Assistente MedCare
           │
           ▼
Gemini API
           │
           ▼
Classificação Inteligente
           │
           ▼
Jornada Inteligente
           │
           ▼
Resumo em PDF
```

---

## 5.3. Estrutura de Desenvolvimento

Durante o desenvolvimento foram adotados os seguintes padrões:

- Arquitetura modular;
- Controllers separados por contexto;
- Models utilizando PascalCase;
- Métodos utilizando camelCase;
- Rotas utilizando Resource Controllers;
- Formulários implementados com `useForm` do Inertia.js;
- Componentes reutilizáveis;
- Organização por módulos;
- Controle de versão utilizando Git.

Workflow adotado:

```
master
   │
develop
   │
feature/nome-da-funcionalidade
```

Todo desenvolvimento ocorre na branch **develop**, sendo a **master** destinada apenas para versões estáveis.

---

# 6. Considerações Finais

O MedCare Digital demonstra como uma arquitetura moderna baseada em Laravel, Vue e Inertia pode ser utilizada para desenvolver um sistema distribuído voltado à organização da jornada pessoal de saúde.

A divisão em módulos independentes facilita futuras expansões da plataforma, permitindo novas integrações e funcionalidades sem comprometer a estrutura existente.

A utilização da Inteligência Artificial auxilia na classificação automática das informações, tornando a interação mais intuitiva para o usuário, enquanto a Jornada Inteligente reúne os dados cadastrados em um resumo organizado, proporcionando maior controle sobre as informações de saúde.

O projeto foi desenvolvido com foco em organização, escalabilidade, manutenção e boa experiência do usuário, servindo como base para futuras integrações com serviços externos, como a API oficial do WhatsApp e outras plataformas da área da saúde.
