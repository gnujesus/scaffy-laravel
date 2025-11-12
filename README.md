# Scaffy Laravel Adapter


[![Latest Version on Packagist](https://img.shields.io/packagist/v/gnu/scaffy-laravel.svg?style=flat-square)](https://packagist.org/packages/gnu/scaffy-laravel)
[![License](https://img.shields.io/github/license/gnujesus/scaffy-laravel?style=flat-square)](https://github.com/gnujesus/scaffy-laravel/blob/main/LICENSE)
[![Laravel](https://img.shields.io/badge/laravel-10%20|%2011%20|%2012-orange?style=flat-square&logo=laravel)](https://laravel.com/)
[![Downloads](https://img.shields.io/packagist/dt/gnu/scaffy-laravel.svg?style=flat-square)](https://packagist.org/packages/gnu/scaffy-laravel)


**Scaffy Laravel** is a Laravel-specific adapter for the [Scaffy Core](https://github.com/gnujesus/scaffy-core) code generation system. It provides an Artisan command to generate Eloquent models directly from your database schema.

---

## ✨ Features

- 🎯 Generate models from **SQL Server**, **PostgreSQL**, or **MySQL**
- 🔌 Fully decoupled via **Hexagonal Architecture**
- 🔍 Analyzes your database structure using Laravel's DB layer
- ⚙️ Pluggable support for custom adapters and databases
- 🛠️ Uses Laravel's Artisan Console Command

---

## 🚀 Installation

```bash
composer require gnu/scaffy-laravel
```

Scaffy Laravel will be auto-discovered by Laravel.

---

## 📦 Requirements


- PHP 8.1+
- Laravel 10, 11, or 12
- One of:
  - SQL Server (`sqlsrv`)
  - PostgreSQL (`pgsql`)
  - MySQL (`mysql`)

---

## 🧰 Usage

```bash
php artisan scaffy:generate --schema=your_schema

```

### Options

| Option             | Description                                                              |
|--------------------|--------------------------------------------------------------------------|
| `--schema`         | The database schema to use                                               |
| `--table`          | Only generate a model for a specific table                               |
| `--output`         | (Coming soon) Output path for generated models (default: `app/Models`)   |
| `--with-relations` | (Coming soon) Generate relationships between models                      |


---

## ⚙️ Configuration & Binding

Scaffy binds a default database adapter based on your `DB_CONNECTION`.

To override or extend:

```php
use Gnu\Scaffy\Laravel\Ports\DatabasePort;

use App\Adapters\CustomPostgresAdapter;

$this->app->bind(DatabasePort::class, fn () => new CustomPostgresAdapter());
```


---

## 🧱 Architecture

Scaffy follows **Hexagonal Architecture**:

- `scaffy-core`: Business logic & interfaces
- `scaffy-laravel`: Laravel integration (command, container)
- Future: CLI, Symfony, CodeIgniter, standalone PHP

---

## 🧪 Example Output


```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $fillable = [
        'id',
        'name',
        'email'
    ];


    public $timestamps = false;

    public function getTable()
    {
        return 'dbo.users';
    }

}
```

---

## 📄 License

MIT © [gnujesus](https://github.com/gnujesus)
