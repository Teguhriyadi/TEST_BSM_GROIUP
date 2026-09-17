<?php

namespace Database\Factories;

use App\Models\Cabang;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $cabang = Cabang::inRandomOrder()->first() ?? Cabang::create([
            'id' => Str::uuid()->toString(),
            'kode_cabang' => 'CAB-FAC-' . Str::random(3),
            'nama_cabang' => fake()->city() . ' Branch',
            'alamat' => fake()->address(),
            'telepon' => fake()->phoneNumber(),
            'is_active' => '1',
        ]);

        $role = Role::inRandomOrder()->first() ?? Role::create([
            'id' => Str::uuid()->toString(),
            'kode_role' => 'ROL-FAC-' . Str::random(3),
            'nama_role' => 'Staff',
            'is_active' => '1',
        ]);

        return [
            'cabang_id' => $cabang->id,
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => $role->id,
            'nomor_hp' => fake()->numerify('08##########'),
            'is_active' => '1',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
