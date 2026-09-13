<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->businesses()
            ->where('businesses.id', app('currentBusiness')->id)
            ->wherePivot('status', 'active')
            ->exists();
    }

    public function view(User $user, Branch $branch): bool
    {
        return $this->belongsToCurrentBusiness($branch);
    }

    public function create(User $user): bool
    {
        return $this->hasManagementAccess($user);
    }

    public function update(User $user, Branch $branch): bool
    {
        return $this->belongsToCurrentBusiness($branch)
            && $this->hasManagementAccess($user);
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $this->belongsToCurrentBusiness($branch)
            && $this->hasManagementAccess($user);
    }

    private function belongsToCurrentBusiness(Branch $branch): bool
    {
        return $branch->business_id === app('currentBusiness')->id;
    }

    private function hasManagementAccess(User $user): bool
    {
        $business = app('currentBusiness');

        return $user->businesses()
            ->where('businesses.id', $business->id)
            ->wherePivot('status', 'active')
            ->wherePivotIn('role', [
                'owner',
                'admin',
                'manager',
            ])
            ->exists();
    }
}