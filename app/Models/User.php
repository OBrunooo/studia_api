<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\PasswordResetNotification;
use App\Models\Tema;
use App\Models\UserTema;
use Illuminate\Support\Facades\DB;
use App\Models\UserConclusaoConjunto;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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


    public function sendPasswordResetToken($token)
    {
        $this->notify(new PasswordResetNotification($token));
    }

    public function temasUser() {
        return DB::table(UserTema::TABLE. " as ut")
        ->join("temas as t", "t.id", "=", "ut.tema_id")
        ->where("ut.user_id", $this->id)
        ->select("ut.tema_id as id", "t.nome as nome")
        ->get();
    }
    
    public function conclusaoTemasUser($temaId) {
        try {
        $tema = Tema::find($temaId);
        $conjuntos = $tema->conjuntosTema();
        if(empty($conjuntos)) {
            return null;
        }
        $conjuntoConcluido = UserConclusaoConjunto::where("user_id", $this->id)->whereIn("conjunto_id", $conjuntos)->where("conclusao", true)->get()->toArray();
        $porcentagemConclusao = (int) ((count($conjuntoConcluido) / count($conjuntos)) * 100);
        return $porcentagemConclusao;
        } catch (\Exception $e) {
            return null;
        }
    }
}
