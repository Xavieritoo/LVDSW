<?php

namespace App\DAO;

use Illuminate\Support\Facades\DB;

class PasswordResetDAO
{
    public function updateOrInsert($email, $tokenHasheado)
    {
        $sql = "INSERT INTO password_resets (email, token, created_at)
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE token = ?, created_at = NOW()";
        DB::update($sql, [$email, $tokenHasheado, $tokenHasheado]);
    }

    public function obtenerPorEmail($email)
    {
        $sql = "SELECT * FROM password_resets WHERE email = ? LIMIT 1";
        $result = DB::select($sql, [$email]);
        return $result ? $result[0] : null;
    }

    public function eliminarPorEmail($email)
    {
        $sql = "DELETE FROM password_resets WHERE email = ?";
        DB::delete($sql, [$email]);
    }
}
