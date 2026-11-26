<div>
    <div class="page-head">
        <div>
            <h1>{{ __('Settings') }}</h1>
            <p>{{ __('Store information, roles and staff access.') }}</p>
        </div>
    </div>

    <div class="bottom-row">
        <div class="card">
            <div class="card-head">
                <h2>{{ __('Store') }}</h2>
                <p>{{ __('Basic details used across receipts and the dashboard') }}</p>
            </div>
            <div class="card-body">
                <div class="field">
                    <label>{{ __('Store name') }}</label>
                    <input type="text" class="input" value="{{ config('app.name') }}" disabled>
                    <span class="hint">{{ __('Set via the APP_NAME environment variable.') }}</span>
                </div>
                <div class="field">
                    <label>{{ __('Currency') }}</label>
                    <input type="text" class="input" value="{{ __('Indonesian Rupiah (Rp)') }}" disabled>
                </div>
                <div class="field">
                    <label>{{ __('Timezone') }}</label>
                    <input type="text" class="input" value="{{ config('app.timezone') }}" disabled>
                </div>
                <p class="hint">{{ __('Editable store settings are on the roadmap — this page currently reflects your configuration.') }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2>{{ __('Roles') }}</h2>
                <p>{{ __('Access levels configured for this store') }}</p>
            </div>
            <div class="tx-list">
                @forelse ($roles as $role)
                    <div class="tx-row">
                        <p class="tx-name">{{ $role->name }}</p>
                        <span class="badge badge-neutral">{{ __(':count user(s)', ['count' => $role->users_count]) }}</span>
                    </div>
                @empty
                    <p class="stock-empty">{{ __('No roles configured yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:16px;">
        <div class="card-head">
            <h2>{{ __('Staff') }}</h2>
            <p>{{ __('Everyone with access to this store') }}</p>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Username') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="cell-strong">{{ $user->name }}</td>
                            <td class="cell-muted">{{ $user->username }}</td>
                            <td>{{ $user->role?->name ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $user->status === 'active' ? 'badge-good' : 'badge-neutral' }}">{{ \App\Support\Labels::userStatus($user->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><p class="table-empty">{{ __('No staff yet.') }}</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
