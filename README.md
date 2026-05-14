# Projeto MedCare Digital com Laravel 11, Inertia.js e Vue 3

## Requisitos

- **PHP**: 8.1 ou superior
- **Composer**: 2.x ou superior
- **Node.js**: 18.x ou superior
- **NPM/Yarn**: 8.x (para NPM) ou 1.x (para Yarn)
- **MySQL**: 8.x (ou outro banco de dados compatível)
- **Git**: Controle de versão para gerenciar o código.

## Tecnologias Utilizadas

- **[Laravel 11](https://laravel.com/docs/11.x)**: Um framework PHP moderno para desenvolvimento web back-end, focado em simplicidade e velocidade.  
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Laravel Downloads"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Laravel Version"></a>
  <a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="Laravel License"></a>

- **[Inertia.js](https://inertiajs.com/docs)**: Uma ponte entre o front-end e back-end que permite construir aplicações SPA sem necessidade de APIs separadas.  
  <a href="https://www.npmjs.com/package/@inertiajs/inertia"><img src="https://img.shields.io/npm/dt/@inertiajs/inertia" alt="Inertia Downloads"></a>
  <a href="https://www.npmjs.com/package/@inertiajs/inertia"><img src="https://img.shields.io/npm/v/@inertiajs/inertia" alt="Inertia Version"></a>
  <a href="https://github.com/inertiajs/inertia/blob/master/LICENSE"><img src="https://img.shields.io/npm/l/@inertiajs/inertia" alt="Inertia License"></a>

- **[Vue 3](https://vuejs.org/guide/introduction.html)**: Um framework JavaScript para construção de interfaces interativas e reativas de maneira eficiente.  
  <a href="https://www.npmjs.com/package/vue"><img src="https://img.shields.io/npm/dt/vue" alt="Vue Downloads"></a>
  <a href="https://www.npmjs.com/package/vue"><img src="https://img.shields.io/npm/v/vue" alt="Vue Version"></a>
  <a href="https://github.com/vuejs/vue/blob/main/LICENSE"><img src="https://img.shields.io/npm/l/vue" alt="Vue License"></a>

- **[Jetstream](https://jetstream.laravel.com/2.x/introduction.html)**: Um pacote de scaffolding para Laravel que fornece autenticação, registro, recuperação de senha e gerenciamento de perfis.  
  <a href="https://packagist.org/packages/laravel/jetstream"><img src="https://img.shields.io/packagist/dt/laravel/jetstream" alt="Jetstream Downloads"></a>
  <a href="https://packagist.org/packages/laravel/jetstream"><img src="https://img.shields.io/packagist/v/laravel/jetstream" alt="Jetstream Version"></a>
  <a href="https://github.com/laravel/jetstream/blob/2.x/LICENSE.md"><img src="https://img.shields.io/github/license/laravel/jetstream" alt="Jetstream License"></a>

- **[PrimeVue](https://primevue.org/documentation/)**: Uma biblioteca de componentes UI para Vue que oferece componentes prontos e customizáveis para interfaces modernas.  
  <a href="https://www.npmjs.com/package/primevue"><img src="https://img.shields.io/npm/dt/primevue" alt="PrimeVue Downloads"></a>
  <a href="https://www.npmjs.com/package/primevue"><img src="https://img.shields.io/npm/v/primevue" alt="PrimeVue Version"></a>
  <a href="https://github.com/primefaces/primevue/blob/master/LICENSE.md"><img src="https://img.shields.io/npm/l/primevue" alt="PrimeVue License"></a>

## Instalação

1. Clone o repositório em sua máquina local:

```bash
git clone https://github.semsa/base-projects/template-laravel11-sakai-vue.git
```

2. Instale as bibliotecas de PHP do projeto com Composer:

```bash
composer update
```

3. Instale as bibliotecas de JavaScript com npm ou yarn:

```bash
npm install
```

## Configuração Inicial

Se a instalação for bem sucedida, comece a configuração abaixo:

1. Crie o arquivo `.env` na raiz do projeto a partir de uma **cópia de `.env.example`**:

```bash
cp .env.example .env
```

    Importante: as variáveis de ambiente em ".env" podem e devem ser alteradas de acordo com a necessidade da aplicação, como para ativar/desativar funcionalidades e alterar comportamentos da aplicação. Ex:

    APP_NAME - Altera o nome da aplicação na barra do navegador e em outras partes da aplicação, que é visível para o usuário final
    APP_ENV - Pode ser local, develop e production - Muda a apresentação de erros, a compressão de dados e a otimização para usuário final
    APP_DEBUG - Ativa ou desativa o modo debug, com apresentação de erros na tela para o usuário final (uso em local e develop)
    APP_LOCALE - Altera as respostas padrão e as formatações de texto da aplicação
    DB_CONNECTION - Define a conexão padrão do banco, cujo nome deve ser igual a configuração definida em "config/database.php" - Usada em todos os Model com conexão "default"
    ...

    Para configurações mais específicas, procure na pasta "config/*" o arquivo da biblioteca desejada e altere as variáveis. As configurações mais globais já estão associadas a uma váriavel de ambiente do ".env", portanto a maioria dos casos você só precisa alterar o que precisa em ".env".

2. Após criar o arquivo ".env", deve-se gerar a **APP_KEY**, se ainda não existir uma no ".env":

```bash
php artisan key:generate
```

3. Para que as **imagens públicas** (da pasta storage/public) sejam exibidas na aplicação, deve-se criar link simbólico:

```bash
php artisan storage:link
```

4.  A configuração do banco deve ser feita no ".env", alterado as seguintes variaveis:  
    DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD.

        - Para este exemplo, pode ser usando a `DB_CONNECTION=sqlite` e comentar as outras variáveis
        - Caso queria usar o seu Banco, altere todos as variáveis para efetuar a conexão

5.  Com a configuração do banco efetuada, algumas tabelas devem ser criadas para que a **autenticação, sessão e cache** padrão do Laravel funcione. Para isto, rode as migrações:

```bash
php artisan migrate
```

## Execução em Desenvolvimento

Com a configuração do banco finalizada e bem sucedida, pode-se abrir o servidor de desenvolvimento:

1. Inicie o Servidor Laravel:

```bash
php artisan serve
```

2. Inicie o Vite:

```bash
npm run dev
```

3. Abra a página inicial da aplicação em: http://127.0.0.1:8000

Obs: você pode começar a popular o banco se registrando na aplicação pelo tela de registro (funcional desde o início da aplicação) e depois pode apagar ou alterar as páginas de login e registro em `resources/js/Pages/Auth/*`.

4. Inicie o desevolvimento de sua aplicação
   - altere as rotas em `routes/web.php`
   - crie **Models, Views, Controllers e outros** com Artisan CLI:
     - `php artisan make:controller Nome` - criar controller
     - `php artisan make:model Nome` - criar model
     - `php artisan make:view Nome` - criar view
     - `php artisan` - listar os comandos disponíveis.
   - veja abaixo os padrões de desenvolvimendo adotados

## Padrões de Construção de Código

### Nomes de Modelos

Os nomes dos _Modelos_, _Controllers_, _Requests_ e _Policies_ devem sempre começar com letra maiúscula e seguir o padrão PascalCase.

**Exemplo**:

```php
class Product extends Model{};
class ProductController extends Controller{};
class StoreProductRequest extends FormRequest{};
class ProductPolicy {};
```

### Nomes de Funções e Métodos

Devem ser escritos com letra minúscula e seguir o padrão camelCase (ex: `getUser`, `calculateTotal`).

**Exemplo**

```php
public function calculateTotal($productList)
```

### Nomes de Variáveis

Devem ser descritivos, refletindo o propósito no algoritmo, e seguir o padrão camelCase (ex: `userList`, `totalAmount`).

```php
$totalAmount = $this->calculateTotal($productsInStock);
```

### Organização de Arquivos

Separe Models, Controllers e Pages por contexto de uso. Organize cada módulo de forma clara e modular, facilitando a manutenção e compreensão do código.
Ao criar um módulo de gerenciamento de produtos, todos os arquivos relacionados devem estar em uma pasta chamada Produto (PascalCase).

A estrutura dos arquivos deve ser:

### Exemplo de Organização de Arquivos:

Ao criar um módulo de gerenciamento de produtos, todos os arquivos relacionados devem estar organizados dentro de uma pasta chamada `Produto` (PascalCase). A estrutura e nomeação dos arquivos deve seguir os seguintes padrões:

### Organização de arquivos Frontend

1. **Index.vue**: Página principal do módulo, responsável por listar os produtos. Podendo conter funcionalidades como filtros, além de botões para adicionar, editar e excluir produtos, caso essas ações estejam disponíveis.
2. **Create.vue**: Página destinada à criação de um novo produto.
3. **Edit.vue**: Página utilizada para editar um produto existente.

```
<!-- Podendo separar as partes de cada tela padrão em componentes -->
resources/js/Pages/Produto/
    ├── Index.vue
    ├── Create.vue
    └── Edit.vue
```

_Observações_: Em todos os requests serão utilizados o <a href="https://inertiajs.com/forms">useForm</a> no Inertia, sendo **OBRIGATÓRIO** o uso do _Form Helper_ <a href="https://inertiajs.com/forms#form-helper">https://inertiajs.com/forms#form-helper</a>

4. **Delete.vue**: Modal utilizado para confirmar exclusão de um produto existente. (Caso exista a funcionalidade);

```
<!-- Pode conter outros componentes que estão envolvido no contexto de produtos -->
resources/js/Components/Produto/
    ├── Delete.vue
    └── FormProduto.vue
```

### Organização de arquivos Backend

Deve seguir os mesmos padrões citados para o frontend. Contendo apenas ações de CRUD relativos ao contexto aplicado;

**Exemplo de controllers**

```
<!-- Nome do controller deve conter o contexto de ações utilizadas dentro do arquivo -->
app/http/Controllers/Produto/
    └── ProdutoController.vue
```

_Observações_: <u>em contextos de ações que envolvem duas ou mais tabelas/modelo deve-se criar outro controller com o nome do modelo Mandatório na frente. Ex:</u>

```
<!-- Controller que realiza inclusões, alterações e exclusões de produtos em um carrinhos de compras -->
app/http/Controllers/Produto/
    └── CarrinhoProdutoController.vue
```

**Exemplo de Validações**

```
<!-- Nome do request Validator deve conter o nome da ação utilizada naquele requests específico -->
app/http/Requests/Produto/
    ├── StoreProdutoRequest.php
    ├── UpdateProdutoRequest.php
    └── DeleteProdutoRequest.php
```

### Organização de ROTAS de aplicação

Deve seguir a lógica de contexto e agrupamento utilizados no backend e no frontend. A escrita deve **OBRIGATORIAMENTE** seguir o padrão de <a href="https://laravel.com/docs/11.x/controllers#resource-controllers">Resource Controllers</a> conforme documentação do laravel.

**Exemplo de rotas**

```php
// Agrupar por contexto utilizando função PREFIX
// Opção 1
Route::prefix('products')->group(function(){
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    //... demais rotas conforme documentação do laravel;
});

// Opção 2 (Usando Função Resource)
Route::Resource('products',ResourceController::class);->only(['index']);
// OBRIGATORIAMENTE sinalizado com ONLY ou EXCEPT quais rotas estão disponíveis no resource
```

Em caso de contexto duplo utilizar mais de um prefix. Ex:

```php
Route::prefix('/cart')->prefix('/products')->group(function(){
    Route::get('/', [ProductController::class, 'index'])->name('products.index');
    //... demais rotas conforme documentação do laravel;
});
```

## Padronização de arquivos auxiliares para o FRONTEND

Deve seguir a lógica de contexto e agrupamento utilizados em todo o sistema.

### Para funções, composables e listas globais

```
<!-- PATH que contém funções separados por contexto de funcionalidade -->
resources/js/Assets/
    ├── Composables.js
    ├── States.js
    ├── DateFunctions.js
    ├── MoneyFunction.js
    ├── Types.js
    └── Lists.js <!-- computeds com listagens fixas globais para o sistema  -->
```

**Em caso de implementação de composables, funções, formulários e lista deve-se criar no PATH de contexto dos componentes**

```
resources/js/Components/Produto/
    ├── ProdutoComposable.js
    ├── ProdutoFunction.js
    ├── ProdutoForm.js
    ├── ProdutoState.js
    ├── ProdutoType.ts
    └── ProdutoList.js
```

Para implementação de formulário utilizar **OBRIGATORIAMENTE** o useForm utilizando Helpers
<a href="https://inertiajs.com/forms#form-helper">https://inertiajs.com/forms#form-helper</a>
