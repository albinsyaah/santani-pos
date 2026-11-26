<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('email', 'username');
        });

        // Existing accounts keep working: "owner@agropos.test" becomes "owner".
        // Clashes (two accounts with the same part before the @) get the id appended.
        $taken = [];

        foreach (DB::table('users')->orderBy('id')->get(['id', 'username']) as $user) {
            $username = Str::lower(Str::before($user->username, '@'));

            if ($username === '' || isset($taken[$username])) {
                $username .= $user->id;
            }

            $taken[$username] = true;

            DB::table('users')->where('id', $user->id)->update(['username' => $username]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('username', 'email');
        });
    }
};
