<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
// use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\HasUuid; // <--- 1. Import Trait
use App\Models\Student;
use App\Models\Classroom;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'phone',
        'photo',
        'address',
        'jenis_user',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    protected static function booted()
    {
        static::saving(function ($user) {
            // Jika username belum diisi, ambil angka NIS dari bagian sebelum '@' di email
            if (empty($user->username) && !empty($user->email)) {
                $user->username = explode('@', $user->email)[0];
            }
        });
    }

    public function classroom()
    {
        return $this->hasOneThrough(
            Classroom::class,
            Student::class,
            'user_id',      // Foreign key di tabel students yang mengarah ke users
            'id',           // Foreign key di tabel classrooms (id)
            'id',           // Local key di tabel users
            'classroom_id'  // Local key di tabel students
        );
    }



}
