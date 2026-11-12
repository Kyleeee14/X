<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('admin:create {email} {--name=Vibe Security} {--username=vibe_security}')]
#[Description('Buat akun admin khusus atau promosikan akun dengan email yang sama')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Alamat email tidak valid.');
            return self::FAILURE;
        }

        if ($user = User::where('email', $email)->first()) {
            $user->forceFill(['is_admin' => true])->save();
            $this->info("Akun {$email} sekarang memiliki akses admin.");
            return self::SUCCESS;
        }

        $base = Str::slug((string) $this->option('username'), '_') ?: 'vibe_security';
        $username = $base;
        $suffix = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base.'_'.$suffix++;
        }

        $password = Str::password(20, symbols: false);
        User::create([
            'name' => (string) $this->option('name'),
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ])->forceFill(['is_admin' => true])->save();

        $this->newLine();
        $this->info('Akun admin berhasil dibuat. Simpan kredensial berikut sekarang:');
        $this->line("Email    : {$email}");
        $this->line("Username : {$username}");
        $this->line("Password : {$password}");
        $this->warn('Password hanya ditampilkan satu kali. Ganti setelah login.');
        return self::SUCCESS;
    }
}
