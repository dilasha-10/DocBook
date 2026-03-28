<?php
class UserModel
{
    private string $usersFile;
    private string $tokensFile;

    public function __construct()
    {
        $storageDir = dirname(__DIR__) . '/storage';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0775, true);
        }

        $this->usersFile = $storageDir . '/users.json';
        $this->tokensFile = $storageDir . '/tokens.json';

        if (!file_exists($this->usersFile)) {
            file_put_contents($this->usersFile, '{}');
        }

        if (!file_exists($this->tokensFile)) {
            file_put_contents($this->tokensFile, '{}');
        }
    }

    public function getUsers(): array
    {
        return json_decode(file_get_contents($this->usersFile), true) ?? [];
    }

    public function saveUsers(array $users): void
    {
        file_put_contents($this->usersFile, json_encode($users, JSON_PRETTY_PRINT));
    }

    public function findByEmail(string $email): ?array
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            if (($user['email'] ?? '') === $email) {
                return $user;
            }
        }

        return null;
    }

    public function emailExists(string $email): bool
    {
        return $this->findByEmail($email) !== null;
    }

    public function createUser(string $name, string $email, string $hashedPassword, string $role): array
    {
        $users = $this->getUsers();
        $userId = count($users) + 1;

        $user = [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword,
            'role' => $role,
            'createdAt' => date('c'),
        ];

        $users[(string) $userId] = $user;
        $this->saveUsers($users);

        return $user;
    }

    public function createToken(int $userId, string $email, string $role): string
    {
        $token = bin2hex(random_bytes(32));
        $tokens = json_decode(file_get_contents($this->tokensFile), true) ?? [];

        $tokens[$token] = [
            'userId' => $userId,
            'email' => $email,
            'role' => $role,
            'created' => time(),
            'expires' => time() + (24 * 3600),
        ];

        file_put_contents($this->tokensFile, json_encode($tokens, JSON_PRETTY_PRINT));
        return $token;
    }

    public function verifyToken(string $token): ?array
    {
        $tokens = json_decode(file_get_contents($this->tokensFile), true) ?? [];

        if (!isset($tokens[$token])) {
            return null;
        }

        $tokenData = $tokens[$token];
        if (($tokenData['expires'] ?? 0) < time()) {
            unset($tokens[$token]);
            file_put_contents($this->tokensFile, json_encode($tokens, JSON_PRETTY_PRINT));
            return null;
        }

        return $tokenData;
    }
}
