<?php

namespace App\Traits;

use App\Services\SiteService;
use App\Services\BackupService;

trait SiteBackupTrait
{
    public function backupPath(SiteService $siteService): string
    {
        return '/opt/stack/sites/' . $siteService->name() . '/public';
    }

    public function backupRepo(BackupService $backupService, SiteService $siteService): string
    {
        if ($backupService->config()['strategy'] == 's3') {
            return 's3:' . $backupService->config()['s3']['bucket'] . '/sites/' . $siteService->name();
        } else {
            return '/opt/stack/backups/sites/' . $siteService->name();
        }
    }
}
