<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\User;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use Illuminate\Mail\Attachment;


class SendDmeQrCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $qrImageUrl;     // ← URL de l'image générée
    public $downloadUrl;
    protected $fromUser;

    protected $toUser;

    public function __construct(string $qrCodeBase64, string $downloadUrl, User $fromUser, User $toUser)
    {
        $this->qrImageUrl = self::saveBase64UrlImage($qrCodeBase64);
        $this->downloadUrl = $downloadUrl;
        $this->fromUser = $fromUser;
        $this->toUser = $toUser;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Partage de dossier médical via QR Code',
            replyTo: [
                new Address($this->fromUser->email, $this->fromUser->name),
            ]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.send_dme_qrcode',
            with: [
                'qrImageUrl' => $this->qrImageUrl,
                'downloadUrl' => $this->downloadUrl,
                'userName' => $this->fromUser->name,
                'toUserName' => $this->toUser->name,
                'logoSquare' => public_path('images/logo-square.png'),
                'logoWhite' => public_path('images/logo-white.png')
            ]
        );
    }

    public function attachments(): array
    {
        return [
        ];
    }

    // Méthode static pour convertir et sauvegarder l’image
    protected static function saveBase64UrlImage(string $base64url, string $folder = 'patient_files/qrcodes'): string
    {
        if (str_starts_with($base64url, 'data:image')) {
            [, $base64url] = explode(',', $base64url);
        }

        $base64 = strtr($base64url, '-_', '+/');
        $padding = strlen($base64) % 4;
        if ($padding > 0) {
            $base64 .= str_repeat('=', 4 - $padding);
        }

        $imageName = uniqid('qr_') . '.png';
        Storage::disk('local')->put("{$folder}/{$imageName}", base64_decode($base64));

        // retourne une URL HTTP dynamique utilisable dans un e-mail
        return URL::route('qrcodes.show', ['filename' => $imageName]);
    }
}

