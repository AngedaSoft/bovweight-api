<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo enviado por el administrador cuando crea un nuevo usuario.
 *
 * Contiene la contrasena en texto plano: es la unica oportunidad para
 * compartirla con el usuario. Despues solo queda el hash. Por eso el
 * mailable NO persiste el password mas alla del envio.
 */
class CredencialesUsuarioMailable extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly User $usuario,
        public readonly string $contrasenaTextoPlano,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tus credenciales para BovWeight CR',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.credenciales-usuario',
            with: [
                'nombre' => $this->usuario->nombre_completo,
                'correo' => $this->usuario->correo,
                'contrasena' => $this->contrasenaTextoPlano,
                'rol' => $this->usuario->rol,
                'loginUrl' => config('app.url'),
            ],
        );
    }
}
