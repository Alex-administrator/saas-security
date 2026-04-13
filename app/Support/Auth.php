<?php
declare(strict_types=1);

namespace App\Support;

use App\Repositories\OrganizationRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\UserRepository;
use Throwable;

final class Auth
{
    private static ?array $user = null;
    private static ?array $organization = null;
    private static ?array $membership = null;
    private static ?array $subscription = null;

    public static function boot(): void
    {
        $userId = Session::get('auth.user_id');
        $organizationId = Session::get('auth.organization_id');

        if (!$userId || !$organizationId) {
            return;
        }

        try {
            $userRepository = new UserRepository();
            $organizationRepository = new OrganizationRepository();
            $subscriptionRepository = new SubscriptionRepository();

            self::$user = $userRepository->findById((int) $userId);
            self::$organization = $organizationRepository->findById((int) $organizationId);
            self::$membership = $organizationRepository->findMembership((int) $userId, (int) $organizationId);
            self::$subscription = $subscriptionRepository->findCurrentForOrganization((int) $organizationId);
        } catch (Throwable $exception) {
            Logger::warning('Unable to bootstrap auth context', ['message' => $exception->getMessage()]);
            self::logout();
        }
    }

    public static function login(array $user, array $organization, array $membership, ?array $subscription = null): void
    {
        Session::regenerate();
        Session::put('auth.user_id', (int) $user['id']);
        Session::put('auth.organization_id', (int) $organization['id']);

        self::$user = $user;
        self::$organization = $organization;
        self::$membership = $membership;
        self::$subscription = $subscription;
    }

    public static function logout(): void
    {
        Session::forget('auth.user_id');
        Session::forget('auth.organization_id');
        self::$user = null;
        self::$organization = null;
        self::$membership = null;
        self::$subscription = null;
    }

    public static function check(): bool
    {
        return self::$user !== null;
    }

    public static function user(): ?array
    {
        return self::$user;
    }

    public static function organization(): ?array
    {
        return self::$organization;
    }

    public static function membership(): ?array
    {
        return self::$membership;
    }

    public static function role(): ?string
    {
        return self::$membership['role'] ?? null;
    }

    public static function subscription(): ?array
    {
        return self::$subscription;
    }

    public static function organizationId(): ?int
    {
        return self::$organization['id'] ?? null;
    }

    public static function userId(): ?int
    {
        return self::$user['id'] ?? null;
    }

    public static function hasAnyRole(array $roles): bool
    {
        return in_array((string) self::role(), $roles, true);
    }
}

