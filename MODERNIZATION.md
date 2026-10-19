# Mystery of Heroes — modernization

This branch prepares the archived game for a modern PHP runtime without changing the original ZIP.

## What was added

- `modernization/compat.php` — temporary compatibility layer for the removed `mysql_*`, `ereg/eregi`, and legacy session APIs.
- `.github/workflows/modernize-legacy.yml` — extracts the ZIP, adds the compatibility layer, enables legacy short PHP tags for the candidate build, checks PHP syntax, and publishes a deployment ZIP as a GitHub Actions artifact.

## Important

The original project is a server-side PHP + MySQL application. GitHub Pages cannot execute PHP or MySQL, so the final playable version needs PHP 8.x + MySQL/MariaDB hosting. GitHub can still store the source code and build the deployment package.

The compatibility layer is transitional. The long-term goal is to replace the old database API with PDO/MySQLi, migrate CP1251 data to UTF-8, remove IE-only browser checks, and replace Flash/SWF functionality with HTML5/JavaScript.

## Next deployment steps

1. Let the `Build PHP 8 candidate` workflow finish on the `modernization` branch.
2. Download the `mystery-of-heroes-php8-candidate` artifact.
3. Create a MySQL/MariaDB database and import `database.sql` after checking its encoding and old MyISAM definitions.
4. Set the database credentials in `conf.php` on the server instead of committing real credentials.
5. Configure the web server document root to the extracted game directory.
6. Test login, city/game pages, chat, inventory, battles, and database writes before exposing the server publicly.

No production credentials are included in this branch.
