<?php

namespace App\Services;

use App\Models\User;

class DepartmentAccessService
{
    public function userHasAccessToDepartment(?User $user, ?string $department): bool
    {
        if (!$user) return false;
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('employee')) {
            return ($user->department ?? '') !== '' && ($department ?? '') === (string) $user->department;
        }
        return false;
    }

    public function filterByDepartmentAccess($query, ?User $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->hasRole('admin')) {
            return $query;
        }
        if ($user->hasRole('employee') && $user->department) {
            return $query->where('department', $user->department);
        }
        return $query->whereRaw('1 = 0');
    }
}


