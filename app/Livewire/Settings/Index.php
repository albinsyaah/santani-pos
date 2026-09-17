<?php

namespace App\Livewire\Settings;

use App\Models\Role;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Settings')]
class Index extends Component
{
    public function roles()
    {
        return Role::withCount('users')->get();
    }

    public function users()
    {
        return User::with('role')->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.settings.index', [
            'roles' => $this->roles(),
            'users' => $this->users(),
        ]);
    }
}
