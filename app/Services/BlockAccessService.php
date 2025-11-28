<?php

namespace App\Services;

use App\Models\User;

class BlockAccessService
{
    /**
     * Проверяет, имеет ли пользователь доступ к указанному блоку
     */
    public function userHasAccessToBlock(?User $user, string $block): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('employee') && $user->block_rank) {
            $userBlockRank = (string) $user->block_rank;
            
            return str_starts_with($block, $userBlockRank . '.') || $block === $userBlockRank;
        }

        return false;
    }

    /**
     * Фильтрует записи по доступным блокам для пользователя
     */
    public function filterByBlockAccess($query, ?User $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('employee') && $user->block_rank) {
            $blockRank = (string) $user->block_rank;
            
            return $query->where(function($q) use ($blockRank) {
                $q->where('block', $blockRank)
                  ->orWhere('block', 'like', $blockRank . '.%');
            });
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Получает список доступных блоков для пользователя
     */
    public function getAccessibleBlocks(?User $user): array
    {
        if (!$user) {
            return [];
        }

        if ($user->hasRole('admin')) {
            return ['1', '2', '3', '4', '5', '6', '7', 
                    '1.01', '1.02', '1.03', '1.04',
                    '2.01', '2.02', '2.03', '2.04', '2.05',
                    '3.01', '3.02', '3.03', '3.04', '3.05',
                    '4.01', '4.02', '4.03', '4.04',
                    '5.01', '5.02', '5.03', '5.04', '5.05', '5.06', '5.07', '5.08', '5.09',
                    '6.01', '6.02', '6.03', '6.04', '6.05', '6.06', '6.07', '6.08', '6.09', '6.10', '6.11',
                    '7.01', '7.02', '7.03'];
        }

        if ($user->hasRole('employee') && $user->block_rank) {
            $rank = (string) $user->block_rank;
            
            $blocks = [$rank];
            
            $subBlocks = match($rank) {
                '1' => ['1.01', '1.02', '1.03', '1.04'],
                '2' => ['2.01', '2.02', '2.03', '2.04', '2.05'],
                '3' => ['3.01', '3.02', '3.03', '3.04', '3.05'],
                '4' => ['4.01', '4.02', '4.03', '4.04'],
                '5' => ['5.01', '5.02', '5.03', '5.04', '5.05', '5.06', '5.07', '5.08', '5.09'],
                '6' => ['6.01', '6.02', '6.03', '6.04', '6.05', '6.06', '6.07', '6.08', '6.09', '6.10', '6.11'],
                '7' => ['7.01', '7.02', '7.03'],
                default => [],
            };
            
            return array_merge($blocks, $subBlocks);
        }

        return [];
    }
}

