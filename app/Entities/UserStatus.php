<?php

namespace App\Entities;

enum UserStatus: string
{
    case OK = "OK";
    case PENDING_REGISTER = "PENDING_REGISTER";
    case PENDING_ACCEPT = "PENDING_ACCEPT";
    case PENDING_EMAIL = "PENDING_EMAIL";
    case PENDING_PWRESET = "PENDING_PWRESET";
    case DENIED = "DENIED";
    case LOCKED = "LOCKED";

    public function getLoginDenyMessage(): ?string
    {
        return match ($this) {
            self::OK, self::PENDING_PWRESET, self::PENDING_EMAIL => null,
            self::PENDING_REGISTER => 'Bitte schließe deine Registrierung zunächst ab, indem du deine E-Mail-Adresse bestätigst!',
            self::PENDING_ACCEPT => 'Dein Konto wurde noch nicht von einem Administrator freigegeben.',
            self::DENIED => 'Deine Registrierung wurde von einem Administrator abgelehnt, da deine Zugehörigkeit nicht bestätigt werden konnte. Wenn du glaubst, dass dies ein Irrtum ist, wende dich bitte an den organisatorischen Hilfedienst.',
            self::LOCKED => 'Dein Konto wurde gesperrt. Wenn du glaubst, dass dies ungerechtfertigt ist, wende dich bitte an den organisatorischen Hilfedienst.'
        };
    }

    public function displayName(): string
    {
        return match ($this) {
            self::OK => 'OK',
            self::PENDING_REGISTER => 'Registrierung ausstehend',
            self::PENDING_ACCEPT => 'Bestätigung ausstehend',
            self::PENDING_EMAIL => 'E-Mail-Bestätigung ausstehend',
            self::PENDING_PWRESET => 'Passwortzurücksetzung ausstehend',
            self::DENIED => 'Abgelehnt',
            self::LOCKED => 'Gesperrt'
        };
    }

    public function badge(): string
    {
        $badge = match ($this) {
            self::OK => '<span class="badge bg-success">%s</span>',
            self::PENDING_REGISTER, self::PENDING_ACCEPT, self::PENDING_EMAIL, self::PENDING_PWRESET => '<span class="badge bg-warning">%s</span>',
            self::DENIED, self::LOCKED => '<span class="badge bg-danger">%s</span>'
        };

        return sprintf($badge, $this->displayName());
    }

    public function isSynchronizable(): bool
    {
        return match ($this) {
            self::OK, self::PENDING_EMAIL, self::PENDING_PWRESET => true,
            self::PENDING_REGISTER, self::PENDING_ACCEPT, self::DENIED, self::LOCKED => false
        };
    }

    public function isTokenized(): bool
    {
        return match ($this) {
            self::PENDING_REGISTER, self::PENDING_EMAIL, self::PENDING_PWRESET => true,
            self::OK, self::PENDING_ACCEPT, self::DENIED, self::LOCKED => false
        };
    }

    public function isReady(): bool
    {
        return match ($this) {
            self::OK, self::PENDING_EMAIL, self::PENDING_PWRESET => true,
            self::PENDING_REGISTER, self::PENDING_ACCEPT, self::DENIED, self::LOCKED => false
        };
    }
}