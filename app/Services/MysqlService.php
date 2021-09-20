<?php

namespace App\Services;

use App\Service;
use Illuminate\Support\Str;

class MysqlService extends Service
{
    protected string $name = 'mysql';

    protected string $displayName = 'MySQL';

    protected array $defaults = [
        'enabled' => false,
        'image' => 'mariadb',
        'tag' => '10.6',
        'database' => '${STACK_MYSQL_DATABASE:-stack}',
        'username' => '${STACK_MYSQL_USERNAME:-admin}',
        'password' => '${STACK_MYSQL_PASSWORD}',
        'root_password' => '${STACK_MYSQL_ROOT_PASSWORD}'
    ];

    protected array $rules = [
        'enabled' => ['required', 'boolean'],
        'image' => ['required', 'string'],
        'tag' => ['required', 'string'],
        'database' => ['required', 'string', 'min:3'],
        'username' => ['required', 'string', 'min:3'],
        'password' => ['required', 'string', 'min:8'],
        'root_password' => ['required', 'string', 'min:8']
    ];

    public function init(): void
    {
        $this->publishEnv([
            'STACK_MYSQL_PASSWORD' => Str::random(18),
            'STACK_MYSQL_ROOT_PASSWORD' => Str::random(18)
        ]);

        $this->publishViews([
            'mysql' => $this->composeFile()
        ]);
    }

    public function queryCommand(string $query): array
    {
        return array_merge(['exec', '-T', $this->name(), 'mysql', '-N', '-u', 'root', '-p' . $this->config()['root_password'] ?? null, '-e'], [$query]);
    }
}
