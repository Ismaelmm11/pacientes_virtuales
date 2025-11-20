<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserInvitation;
use Carbon\Carbon;

class PruneExpiredInvitations extends Command
{
    /**
     * El nombre y la firma del comando de consola.
     * (Así es como lo llamaremos: 'app:prune-invitations')
     */
    protected $signature = 'app:prune-invitations';

    /**
     * La descripción del comando.
     */
    protected $description = 'Borra todas las invitaciones de registro caducadas (más de 24 horas)';

    /**
     * Ejecuta la lógica del comando.
     */
    public function handle()
    {
        $this->info('Borrando invitaciones caducadas...');

        // 1. Define la fecha de corte (todo lo anterior a 24 horas desde ahora)
        $expiration = Carbon::now()->subHours(24);

        // 2. Busca y borra todas las invitaciones que cumplan esa condición
        $count = UserInvitation::where('created_at', '<', $expiration)->delete();

        $this->info("¡Hecho! Se han borrado $count invitaciones caducadas.");
    }
}