<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use DateTime;
use Ramsey\Uuid\Uuid;
use function App\Helpers\getMembership;
use function App\Helpers\getMembershipsByUserId;
use function App\Helpers\hashSSHA;

class User extends Entity
{
    protected $attributes = [
        'id' => null,
        'username' => null,
        'first_name' => null,
        'last_name' => null,
        'email' => null,
        'password' => null,
        'admin' => false,
        'active' => false,
        'email_confirmed' => false,
        'password_reset' => false,
        'image_id' => null,
        'email_notification' => true,
        'email_newsletter' => true,
        'token' => null,
        'registration_date' => null,
        'accept_date' => null,
        'last_login_date' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'username' => 'string',
        'first_name' => 'string',
        'last_name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'admin' => 'boolean',
        'active' => 'boolean',
        'email_confirmed' => 'boolean',
        'password_reset' => 'boolean',
        'image_id' => 'string',
        'email_notification' => 'boolean',
        'email_newsletter' => 'boolean',
        'token' => 'string',
        'registration_date' => 'timestamp',
        'accept_date' => 'timestamp',
        'last_login_date' => 'timestamp',
    ];

    /**
     * Returns the consecutive user identifier used as primary database key.
     *
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->attributes['id'];
    }

    /**
     * Sets the consecutive user identifier.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->attributes['id'] = $id;
    }

    /**
     * Returns the unique non-changeable username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->attributes['username'];
    }

    public function setUsername(string $username): void
    {
        $this->attributes['username'] = $username;
    }

    /**
     * Returns the full name as a concatenated product of the first and last names.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * Returns the first name(s).
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->attributes['first_name'];
    }

    /**
     * Sets the first name(s).
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->attributes['first_name'] = $firstName;
    }

    /**
     * Returns the last (family) name.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->attributes['last_name'];
    }

    /**
     * Sets the last (family) name.
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->attributes['last_name'] = $lastName;
    }

    /**
     * Returns the email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->attributes['email'];
    }

    /**
     * Sets the email.
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->attributes['email'] = $email;
    }

    /**
     * Returns the SSHA-hashed password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->attributes['password'];
    }

    /**
     * Sets a SSHA hash as the new password, or hashes given cleartext password and sets it.
     *
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void
    {
        // Ensure password is stored as hash
        if (!str_starts_with($password, '{SSHA}')) {
            $password = hashSSHA($password);
        }

        $this->attributes['password'] = $password;
    }

    /**
     * Returns whether user is a system-wide administrator.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->attributes['admin'];
    }

    /**
     * Sets user as system-wide administrator.
     *
     * @param bool $admin
     * @return void
     */
    public function setAdmin(bool $admin): void
    {
        $this->attributes['admin'] = $admin;
    }

    /**
     * Returns whether user is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->attributes['active'];
    }

    /**
     * Sets user's active state.
     *
     * @param bool $active
     * @return void
     */
    public function setActive(bool $active): void
    {
        $this->attributes['active'] = $active;
    }

    /**
     * Returns whether user's email is confirmed.
     *
     * @return bool
     */
    public function isEmailConfirmed(): bool
    {
        return $this->attributes['email_confirmed'];
    }

    /**
     * Sets email confirmation status and returns token.
     * Returns null if token was removed.
     *
     * @param bool $confirmed
     * @return string|null
     */
    public function setEmailConfirmed(bool $confirmed): ?string
    {
        $this->attributes['email_confirmed'] = $confirmed;

        if ($confirmed) {
            $this->removeToken();
            return null;
        } else {
            return $this->generateAndSetToken();
        }
    }

    /**
     * Returns whether user's password was requested to be reset.
     *
     * @return bool
     */
    public function isPasswordReset(): bool
    {
        return $this->attributes['password_reset'];
    }

    /**
     * Sets password reset status and returns token.
     * Returns null if token was removed.
     *
     * @param bool $reset
     * @return string|null
     */
    public function setPasswordReset(bool $reset): ?string
    {
        $this->attributes['password_reset'] = $reset;

        if ($reset) {
            return $this->generateAndSetToken();
        } else {
            $this->removeToken();
            return null;
        }
    }

    /**
     * Returns current non-consecutive token used for password resets or email confirmation.
     * Returns null if no token is set.
     *
     * @return ?string
     */
    public function getToken(): ?string
    {
        return $this->attributes['token'];
    }

    /**
     * Generates, sets and returns new token if no token is already set.
     * Returns null if a token is already set.
     *
     * @return ?string
     */
    public function generateAndSetToken(): ?string
    {
        // Check if current token is obsolete
        if (!$this->removeToken()) {
            return $this->getToken();
        }

        $token = Uuid::uuid4()->toString();
        $this->attributes['token'] = $token;
        return $token;
    }

    /**
     * Removes current token if obsolete.
     * Returns true if the token was removed; returns false if the token is still necessary for a transaction and thus non-obsolete.
     *
     * @return bool
     */
    public function removeToken(): bool
    {
        if (!$this->attributes['token']) {
            return true;
        }

        // Check if token is necessary
        if (!$this->isEmailConfirmed() || $this->isPasswordReset()) {
            return false;
        }

        $this->attributes['token'] = null;
        return true;
    }

    public function getImageId(): ?string
    {
        return $this->attributes['image_id'];
    }

    public function setImageId(?string $imageId): void
    {
        $this->attributes['image_id'] = $imageId;
    }

    public function wantsEmailNotification(): bool
    {
        return $this->attributes['email_notification'];
    }

    public function setEmailNotification(bool $emailNotification): void
    {
        $this->attributes['email_notification'] = $emailNotification;
    }

    public function wantsEmailNewsletter(): bool
    {
        return $this->attributes['email_newsletter'];
    }

    public function setEmailNewsletter(bool $newsletter): void
    {
        $this->attributes['email_newsletter'] = $newsletter;
    }

    public function getRegistrationDate(): ?DateTime
    {
        $formattedDate = $this->attributes['registration_date'];
        if (!$formattedDate) return null;

        return DateTime::createFromFormat('Y-m-d H:i:s', $formattedDate);
    }

    public function setRegistrationDate(DateTime $time): void
    {
        $this->attributes['registration_date'] = $time->format('Y-m-d H:i:s');
    }

    public function getAcceptDate(): ?DateTime
    {
        $formattedDate = $this->attributes['accept_date'];
        if (!$formattedDate) return null;

        return DateTime::createFromFormat('Y-m-d H:i:s', $formattedDate);
    }

    public function setAcceptDate(DateTime $time): void
    {
        $this->attributes['accept_date'] = $time->format('Y-m-d H:i:s');
    }

    public function isAccepted(): bool
    {
        return !is_null($this->getAcceptDate());
    }

    public function getLastLoginDate(): ?DateTime
    {
        $formattedDate = $this->attributes['last_login_date'];
        if (!$formattedDate) return null;

        return DateTime::createFromFormat('Y-m-d H:i:s', $formattedDate);
    }

    public function setLastLoginDate(DateTime $time): void
    {
        $this->attributes['last_login_date'] = $time->format('Y-m-d H:i:s');
    }

    /**
     * @return Membership[]
     */
    public function getMemberships(): array
    {
        return getMembershipsByUserId($this->getId());
    }

    public function mayAccept(User $target): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($target->getMemberships() as $membership) {
            $ownMembership = getMembership($this->getId(), $membership->getOrganisationId());
            if ($ownMembership && $ownMembership->getStatus() == MembershipStatus::ADMIN) {
                return true;
            }
        }

        return false;
    }
}