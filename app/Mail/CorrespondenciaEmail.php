<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorrespondenciaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $asunto;
    public $contenido;
    public $archivoAdjunto;

    /**
     * Create a new message instance.
     */
    public function __construct($asunto, $contenido, $archivoAdjunto = null)
    {
        $this->asunto = $asunto;
        $this->contenido = $contenido;
        $this->archivoAdjunto = $archivoAdjunto;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $email = $this->subject($this->asunto)
                      ->html($this->contenido);
        
        // Adjuntar archivo si existe
        if ($this->archivoAdjunto && file_exists($this->archivoAdjunto)) {
            $email->attach($this->archivoAdjunto);
        }
        
        return $email;
    }
}