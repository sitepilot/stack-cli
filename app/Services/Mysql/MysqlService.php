<?php

namespace App\Services\Mysql;

use App\Service;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class MysqlService extends Service
{
    protected array $defaults = [
        'name' => 'mysql',
        'enabled' => false,
        'image' => 'mariadb',
        'tag' => '10.6',
        'workdir' => '/var/lib/mysql',
        'database' => 'stack',
        'username' => 'admin',
        'password' => null,
        'root_password' => null,
        'backup' => [
            'volume' => 'mysql-backup'
        ]
    ];

    protected array $rules = [
        'database' => ['required', 'string', 'min:3'],
        'username' => ['required', 'string', 'min:3'],
        'password' => ['required', 'string', 'min:8'],
        'root_password' => ['required', 'string', 'min:8']
    ];

    public function init(): void
    {
        parent::init();

        $this->setEnv([
            'STACK_MYSQL_PASSWORD' => Str::random(18),
            'STACK_MYSQL_ROOT_PASSWORD' => Str::random(18)
        ]);
    }

    public function environment(): array
    {
        return [
            'MYSQL_DATABASE' => $this->get('database'),
            'MYSQL_USER' => $this->get('username'),
            'MYSQL_PASSWORD' => '${STACK_MYSQL_PASSWORD:?}',
            'MYSQL_ROOT_PASSWORD' => '${STACK_MYSQL_ROOT_PASSWORD:?}',
        ];
    }

    public function volumes(): array
    {
        return [
            'mysql' => '/var/lib/mysql',
            'mysql-backup' => '/opt/stack/backup'
        ];
    }

    public function query(string $query): string
    {
        return $this->exec(['bash', '-c', sprintf('mysql -N -u root -p${MYSQL_ROOT_PASSWORD} -e "%s"', $query)]);
    }

    public function createDatabase(string $name): string
    {
        return $this->query("CREATE DATABASE \`$name\`;");
    }

    public function createUser(string $user, string $password): string
    {
        return $this->query("CREATE USER '{$user}'@'%' IDENTIFIED BY '{$password}';");
    }

    public function grantUser(string $user, string $database): string
    {
        return $this->query("GRANT ALL ON \`$database\`.* TO '{$user}'@'%';");
    }

    public function dropDatabase(string $name): string
    {
        return $this->query("DROP DATABASE \`$name\`;");
    }

    public function dropUser(string $user): string
    {
        return $this->query("DROP USER \`$user\`;");
    }

    public function getDatabases(): Collection
    {
        return collect(explode(PHP_EOL, $this->query("SHOW DATABASES;")))
            ->map(function ($line) {
                if (!empty($line) && !in_array($line, ['sys', 'performance_schema', 'information_schema'])) {
                    return ['name' => $line];
                }
            })->filter()->values();
    }

    public function preBackupCmd(): void
    {
        $databases = $this->getDatabases();

        foreach ($databases as $database) {
            $this->exec(['bash', '-c', sprintf('mysqldump --add-drop-database -u root -p${MYSQL_ROOT_PASSWORD} --databases %1$s > /opt/stack/backup/%1$s.sql', $database['name'])]);
        }
    }

    public function postRestoreCmd(string $database = null): void
    {
        $files = $this->exec(['ls', '/opt/stack/backup']);

        $backups = array_filter(explode(PHP_EOL, $files), function ($value) {
            return preg_match('/^(.*).sql/', $value);
        });

        if ($database) {
            $backups = array_filter($backups, function ($backup) use ($database) {
                return "$database.sql" == $backup;
            });
        }

        if (!count($backups)) {
            if ($database) {
                $msg = sprintf('No backup available for database %s.', $database);
            } else {
                $msg = 'No backups available.';
            }

            abort(1, $msg);
        }

        foreach ($backups as $backup) {
            $this->exec(['bash', '-c', sprintf('mysql -uroot -p${MYSQL_ROOT_PASSWORD} < /opt/stack/backup/%s', $backup)]);
        }
    }
}
