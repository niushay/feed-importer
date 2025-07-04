
# Data Feed Importer

A modular, extensible command-line tool built with Laravel to import data (initially from CSV) into a database. Designed with scalability, queue support, and clean architecture in mind.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
    - [Command Signature](#command-signature)
    - [Options](#options)
    - [Example Usages](#example-usages)
- [Features](#features)
- [File Format Support](#file-format-support)
- [Event Handling](#event-handling)
- [Queue Support](#queue-support)
- [Testing](#testing)
- [Developer Tools](#developer-tools)
- [Code Style & Linting](#code-style--linting)
- [Changing Database](#changing-database)
- [Monitoring & Logging](#monitoring--logging)
- [Extending the Importer](#extending-the-importer)
- [License](#license)

---

## Installation

```bash
composer install
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
sail down --volumes
sail up -d --force-recreate --build
sail composer install
sail artisan migrate
```


---

## Usage

### Command Signature

```bash
sail artisan import:feed 
    {file=storage/app/public/feed.csv : Path to the feed file} 
    {--model=Product : Type of the data (e.g., product, user)} 
    {--with-header=true : Specify if the file has a header row} 
    {--with-queue=false : Whether to run the import in the queue}
```

---

### Options

| Option           | Description                                                                 |
|------------------|-----------------------------------------------------------------------------|
| `file`           | Path to the CSV file to import. Defaults to `storage/app/public/feed.csv`   |
| `--model`        | Model name (e.g., `Product`). Used to resolve the corresponding Importer     |
| `--with-header`  | Boolean flag to indicate if file has headers. Defaults to `true`             |
| `--with-queue`   | Runs the import job using the queue system. Defaults to `false`              |

---

### Example Usages

```bash
# Import with header
sail artisan import:feed storage/app/public/products.csv --model=Product

# Import without header
sail artisan import:feed storage/app/public/products.csv --model=Product --with-header=false

# Queue the import job
sail artisan import:feed storage/app/public/products.csv --with-queue=true
```

---

## Features

- ✅ Modular Importer structure (supports plug-in style service for new formats)
- ✅ Support for large file processing via Laravel Queues
- ✅ Optional header support
- ✅ Laravel Events and Listeners (e.g., for logging or monitoring)
- ✅ Chunk reading + batch inserts for performance
- ✅ Robust validation and error handling
- ✅ Dockerized with Laravel Sail
- ✅ Built-in progress bar
- ✅ Clean and extensible architecture

---

## File Format Support

The current importer supports:

- ✅ CSV

The architecture is ready to extend support for:

- ⏳ JSON
- ⏳ XML
- ⏳ XLS/XLSX
- ⏳ YAML
- ⏳ SQL dump

---

## Event Handling

Events are used for monitoring the import process:

| Event         | Listener Class            | Description                        |
|---------------|---------------------------|------------------------------------|
| `BeforeImport`| `LogImportStarted`        | Log before import begins           |
| `AfterImport` | `LogImportCompleted`      | Log after successful import        |
| `ImportFailed`| `LogImportFailed`         | Log on import exception            |

---

## Queue Support

To process large files without blocking, run with the `--with-queue=true` flag. Then make sure the queue worker is running:

```bash
sail artisan queue:work
```

Monitor jobs using Laravel Telescope at:

```
http://localhost/telescope
```

---

## Testing

We use [Pest](https://pestphp.com) for feature tests.

```bash
sail artisan test --env=testing
```

Test coverage includes:

- ✅ Importing with and without headers
- ✅ Queue dispatching
- ✅ File validation
- ✅ Model resolution
- ✅ Success and failure row tracking

---

## Developer Tools

- 🧪 **Pest** – Minimal testing with expressive syntax
- 🛡 **PHPStan + Larastan** – Static code analysis

Run PHPStan analysis:

```bash
sail php vendor/bin/phpstan analyse --memory-limit=2G
```

---

## Code Style & Linting

Code style is enforced using [Laravel Pint](https://laravel.com/docs/12.x/pint). A GitHub Actions workflow runs Pint on every push and pull request. It can also auto-fix style issues.

---

## Changing Database

You can modify the database in `docker-compose.yml` and re-run the Sail installation:

```bash
sail down
php artisan sail:install
sail up -d
```

Make sure your `.env` or `.env.testing` reflects the new DB settings.

---

## Monitoring & Logging

- **Laravel Telescope**: Available at `http://localhost/telescope`
- **Events**: Used for structured logging

---

## Extending the Importer

To add a new format:

1. Create a new importer class implementing `ImporterInterface`
2. Register it in the `$importerServices` array in `FeedImporter` command
3. Add your own Import class in `App\Imports`
4. Add appropriate event listeners (optional)

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
