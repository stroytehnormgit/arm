<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FixStagesPermission extends Command
{
    protected $signature = 'fix:stages-permission';
    protected $description = 'Добавляет разрешение stages.manage администраторам';

    public function handle()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permission = Permission::firstOrCreate(['name' => 'stages.manage']);
        $this->info('Разрешение stages.manage создано/найдено');

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo('stages.manage');
            $this->info('Разрешение назначено роли admin');
        } else {
            $this->error('Роль admin не найдена!');
            return 1;
        }

        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $admin->givePermissionTo('stages.manage');
            $this->info("Разрешение назначено пользователю: {$admin->email}");
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $this->info('Готово! Разрешение stages.manage назначено всем администраторам.');

        return 0;
    }
}

