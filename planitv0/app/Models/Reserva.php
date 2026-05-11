<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'user_id',
        'enlazada_en',
        'localizador',
        'vuelo_id',
        'vuelo_vuelta_id',
        'origen',
        'destino',
        'fecha_salida',
        'fecha_llegada',
        'estado',
        'plan_tarifa',
        'precio_total',
        'email_contacto',
        'checkin_disponible_desde',
        'checkin_realizado_en',
        'checkin_estado',
        'tarjetas_emitidas',
        'checkin_correo_intentado_en',
        'checkin_correo_estado',
        'checkin_correo_error',
        'equipaje_resumen',
        'asientos_resumen',
        'meteorologia_resumen',
    ];

    protected $casts = [
        'enlazada_en' => 'datetime',
        'fecha_salida' => 'datetime',
        'fecha_llegada' => 'datetime',
        'checkin_disponible_desde' => 'datetime',
        'checkin_realizado_en' => 'datetime',
        'checkin_correo_intentado_en' => 'datetime',
        'tarjetas_emitidas' => 'boolean',
        'precio_total' => 'decimal:2',
    ];

    protected $attributes = [
        'plan_tarifa' => 'planit_easy',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id');
    }

    public function pasajeros()
    {
        return $this->hasMany(ReservaPasajero::class, 'reserva_id');
    }

    public function cancelacion()
    {
        return $this->hasOne(Cancelacion::class, 'reserva_id');
    }

    public function reembolso()
    {
        return $this->hasOne(Reembolso::class, 'reserva_id');
    }

    public function historialEstados()
    {
        return $this->hasMany(ReservaEstadoHistorial::class, 'reserva_id');
    }

    public function checkinEventos()
    {
        return $this->hasMany(\App\Models\CheckinEvento::class, 'reserva_id');
    }

    public function vueloIda()
    {
        return $this->belongsTo(Vuelo::class, 'vuelo_id');
    }

    public function vueloVuelta()
    {
        return $this->belongsTo(Vuelo::class, 'vuelo_vuelta_id');
    }

    public function pasajerosCompra()
    {
        return $this->hasMany(Pasajero::class, 'reserva_id');
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'reserva_id');
    }

    public function duracionMinutos(): int
    {
        if (!$this->fecha_salida || !$this->fecha_llegada) {
            return 0;
        }

        return max(0, $this->fecha_salida->diffInMinutes($this->fecha_llegada, false));
    }

    public function duracionTexto(): string
    {
        $minutos = $this->duracionMinutos();
        $horas = intdiv($minutos, 60);
        $resto = $minutos % 60;

        if ($horas > 0 && $resto > 0) {
            return $horas . ' h ' . $resto . ' min';
        }

        if ($horas > 0) {
            return $horas . ' h';
        }

        return $resto . ' min';
    }

    public function checkinDisponibleDesdeCalculado(): Carbon
    {
        if ($this->checkin_disponible_desde) {
            return $this->checkin_disponible_desde->copy();
        }

        if ($this->asientosIncluidosEnPlan()) {
            $fechaCreacion = $this->created_at;
            if (!$fechaCreacion) {
                $fechaCreacion = now();
            }

            return $fechaCreacion->copy();
        }

        return $this->fecha_salida->copy()->subHours(24);
    }

    public function planTarifaNormalizado(): string
    {
        return mb_strtolower(trim((string) $this->plan_tarifa), 'UTF-8');
    }

    public function nombrePlanTarifa(): string
    {
        $planTarifa = trim((string) $this->plan_tarifa);
        if ($planTarifa === '') {
            $planTarifa = 'Plan no definido';
        }

        return match ($this->planTarifaNormalizado()) {
            'planit_comfort', 'planit comfort' => 'Planit Comfort',
            'planit_easy', 'planit easy' => 'Planit Easy',
            default => $planTarifa,
        };
    }

    public function asientosIncluidosEnPlan(): bool
    {
        return $this->nombrePlanTarifa() === 'Planit Comfort';
    }

    public function checkinRealizado(): bool
    {
        return $this->checkin_estado === 'confirmada' || !is_null($this->checkin_realizado_en);
    }

    public function checkinDisponibleAhora(): bool
    {
        return !$this->checkinRealizado() && now()->greaterThanOrEqualTo($this->checkinDisponibleDesdeCalculado());
    }

    public function checkinTiempoRestanteTexto(): ?string
    {
        if ($this->checkinRealizado() || $this->checkinDisponibleAhora()) {
            return null;
        }

        $ahora = now();
        $desde = $this->checkinDisponibleDesdeCalculado();

        if ($desde->lessThanOrEqualTo($ahora)) {
            return null;
        }

        $totalMinutos = $ahora->diffInMinutes($desde);
        $dias = intdiv($totalMinutos, 1440);
        $horas = intdiv($totalMinutos % 1440, 60);
        $minutos = $totalMinutos % 60;

        $partes = [];

        if ($dias > 0) {
            $partes[] = $dias . ' d';
        }
        if ($horas > 0) {
            $partes[] = $horas . ' h';
        }
        if ($dias === 0 && $minutos > 0) {
            $partes[] = $minutos . ' min';
        }

        return implode(' ', $partes);
    }
}
