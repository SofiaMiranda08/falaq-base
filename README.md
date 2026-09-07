# FalaQ — Sprint 01

Para executar, testar e publicar esta entrega, consulte [LEIA-ME-SPRINT-01.md](LEIA-ME-SPRINT-01.md).

## Instruções originais

```sh
git clone https://github.com/nato-re/falaq-base.git
cd falaq-base
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate

```