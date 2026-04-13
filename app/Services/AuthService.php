<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\OrganizationRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\UserRepository;
use RuntimeException;

final class AuthService
{
    public function attempt(string $email, string $password): array
    {
        $userRepository = new UserRepository();
        $organizationRepository = new OrganizationRepository();
        $subscriptionRepository = new SubscriptionRepository();

        $user = $userRepository->findByEmail($email);
        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            throw new RuntimeException('Неверный email или пароль.');
        }

        $memberships = $organizationRepository->membershipsForUser((int) $user['id']);
        $activeMemberships = array_filter($memberships, static fn(array $m) => (bool) $m['is_active']);
        if ($activeMemberships === []) {
            throw new RuntimeException('Пользователь не привязан ни к одной организации.');
        }

        $membership = array_values($activeMemberships)[0];
        $organization = $organizationRepository->findById((int) $membership['organization_id']);
        $subscription = $subscriptionRepository->findCurrentForOrganization((int) $membership['organization_id']);

        if ($organization === null) {
            throw new RuntimeException('Организация не найдена.');
        }

        return compact('user', 'membership', 'organization', 'subscription');
    }
}

