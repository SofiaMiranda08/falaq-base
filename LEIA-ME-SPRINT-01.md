# Sprint 01 — Estabilização do MVP

Projeto completo baseado em https://github.com/nato-re/falaq-base.

## Correções

- `StorePerguntaRequest`: texto obrigatório, do tipo string, entre 10 e 255 caracteres; evento_id obrigatório e existente em eventos.
- `EventoController::show`: consulta com `where`, `latest`, desempate por ID e `paginate(10)`. Eventos inexistentes retornam 404.
- `eventos/show.blade.php`: envio do evento_id em campo oculto, limites no formulário, preservação do texto após erro e navegação com `$perguntas->links()`.
- O contador usa `$perguntas->total()`, evitando carregar a relação inteira em memória.
- `AppServiceProvider`: paginação com Bootstrap 5, o mesmo framework visual da página.
- Testes de integração incluídos em `tests/Feature/Sprint01Test.php`. O teste inicial também prepara o banco de testes.

A consulta de perguntas não usa `all()` nem `get()`. O `Evento::all()` do método index continua apenas listando os eventos; ele não carrega as perguntas.

## Executar no computador

Requisitos: PHP 8.2 ou superior compatível com composer.lock, Composer e extensões do Laravel, incluindo PDO SQLite, mbstring e XML.

Abra a pasta do projeto no terminal e execute, uma linha por vez:

```sh
composer install
php -r "copy('.env.example', '.env');"
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan serve
```

Copie `.env.example` somente na primeira configuração, para não substituir configurações existentes. Acesse http://127.0.0.1:8000 e abra o evento principal. O seeder cria 5.000 perguntas no evento principal e 5 no workshop. Execute o seed uma única vez em banco novo para evitar duplicação.

Estas telas usam Bootstrap por CDN e precisam de internet para carregar o estilo. Não é necessário compilar assets para visualizar essas telas.

## Conferir os critérios

```sh
php artisan test
```

A suíte usa SQLite em memória, sem alterar o banco da aplicação. Verifica textos inválidos, campos ausentes, evento inexistente, limites aceitos, formulário, ordenação, filtro por evento e páginas 1, 2 e 500 de uma carga com 5.000 perguntas.

**HTTP 422:** o comportamento padrão do FormRequest retorna 422 quando a requisição espera JSON (`Accept: application/json`), como nos testes `postJson`. No formulário HTML, o Laravel redireciona de volta e mostra o erro de validação. Ambos impedem a gravação inválida. A validação do servidor funciona mesmo que alguém desative os limites HTML.

## Resultado da revisão

- Sintaxe PHP verificada nos arquivos da aplicação e dos testes.
- Suíte executada em PHP 8.4.14: **17 testes aprovados e 159 verificações**.
- Inclui validação real do endpoint, renderização Blade e paginação com o banco de testes.
- Revisão das alterações com `git diff --check`, sem erros.

## Publicar e entregar

Este ZIP não realiza fork, push, abertura de PR nem envio ao Classroom.

1. Acesse o repositório original no GitHub e clique em **Fork** na sua conta.
2. Clone o seu fork no computador.
3. Copie o conteúdo desta pasta para a pasta clonada, substituindo os arquivos correspondentes. Mantenha a pasta `.git` criada pelo clone.
4. No terminal da pasta clonada:

```sh
git switch -c sprint-01-estabilizacao
git add app resources tests LEIA-ME-SPRINT-01.md
git commit -m "Corrige validacao e paginacao de perguntas"
git push -u origin sprint-01-estabilizacao
```

5. No GitHub, abra o PR pelo botão **Compare & pull request** ou copie o link da branch do seu repositório.
6. Envie o link no Classroom até **08/09, às 23h59**, conforme o enunciado.

O ZIP contém o código-fonte, os arquivos de dependências e os testes. Não inclui `.env`, banco local, `vendor`, `node_modules` ou histórico `.git`.
