<?php
use PHPUnit\Framework\TestCase;

class SecurityCheck extends TestCase
{
    private $connection;

    protected function setUp(): void
    {
        $this->connection = new PDO('sqlite::memory:');
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->exec("CREATE TABLE accounts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            password_hash TEXT NOT NULL
        )");
    }

    public function testHashValidation(): void
    {
        $pwd = 'mypassword';
        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
        $this->assertTrue(password_verify($pwd, $hashedPwd));
        $this->assertNotEquals($pwd, $hashedPwd);
    }

    public function testUniqueHash(): void
    {
        $pwd1 = 'mypassword';
        $pwd2 = 'mypassword';
        $hash1 = password_hash($pwd1, PASSWORD_DEFAULT);
        $hash2 = password_hash($pwd2, PASSWORD_DEFAULT);
        $this->assertNotEquals($hash1, $hash2);
    }

    public function testNoPlainText(): void
    {
        $user = 'testuser';
        $pwd = 'mypassword';
        $hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);
        $stmt = $this->connection->prepare("INSERT INTO accounts (username, password_hash) VALUES (:user, :hash)");
        $stmt->execute(['user' => $user, 'hash' => $hashedPwd]);
        $stmt = $this->connection->prepare("SELECT password_hash FROM accounts WHERE username = :user");
        $stmt->execute(['user' => $user]);
        $retrievedPwd = $stmt->fetchColumn();
        $this->assertNotEquals($pwd, $retrievedPwd);
    }

    public function testSQLProtection(): void
    {
        $input = "' OR 1=1 --";
        $stmt = $this->connection->prepare("SELECT * FROM accounts WHERE username = :input");
        $stmt->execute(['input' => $input]);
        $result = $stmt->fetchAll();
        $this->assertEmpty($result);
    }

    public function testSpecialCharacterHandling(): void
    {
        $input = "special'chars";
        $stmt = $this->connection->prepare("INSERT INTO accounts (username, password_hash) VALUES (:user, :hash)");
        $stmt->execute(['user' => $input, 'hash' => 'hashedvalue']);
        $stmt = $this->connection->prepare("SELECT username FROM accounts WHERE username = :user");
        $stmt->execute(['user' => $input]);
        $storedUser = $stmt->fetchColumn();
        $this->assertEquals($input, $storedUser);
    }

    public function testInputSanitization(): void
    {
        $input = "<script>alert('test')</script>";
        $isValid = preg_match('/^[a-zA-Z0-9]+$/', $input);
        $this->assertEquals(0, $isValid);
    }

    public function testHTMLFiltering(): void
    {
        $input = "<script>alert('test')</script>";
        $cleanedInput = strip_tags($input);
        $this->assertNotEquals($input, $cleanedInput);
        $this->assertEquals("alert('test')", $cleanedInput);
    }

    public function testHTMLConversion(): void
    {
        $input = "<span>Text</span>";
        $encodedInput = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        $this->assertEquals("&lt;span&gt;Text&lt;/span&gt;", $encodedInput);
    }

    public function testHeaders(): void
    {
        $responseHeaders = [
            'Content-Security-Policy' => "default-src 'self'",
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
        ];
        $this->assertArrayHasKey('Content-Security-Policy', $responseHeaders);
        $this->assertArrayHasKey('X-Content-Type-Options', $responseHeaders);
        $this->assertArrayHasKey('X-Frame-Options', $responseHeaders);
        $this->assertEquals("default-src 'self'", $responseHeaders['Content-Security-Policy']);
        $this->assertEquals('nosniff', $responseHeaders['X-Content-Type-Options']);
        $this->assertEquals('DENY', $responseHeaders['X-Frame-Options']);
    }

    public function testTokenValidation(): void
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['token'] = $token;
        $requestToken = $token;
        $this->assertEquals($_SESSION['token'], $requestToken);
        $invalidToken = bin2hex(random_bytes(32));
        $this->assertNotEquals($_SESSION['token'], $invalidToken);
    }

    public function testTokenExpiration(): void
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['token'] = $token;
        $_SESSION['token_time'] = time();
        $currentTime = $_SESSION['token_time'] + 300;
        $this->assertLessThan(600, $currentTime - $_SESSION['token_time']);
        $currentTime = $_SESSION['token_time'] + 1200;
        $this->assertGreaterThan(600, $currentTime - $_SESSION['token_time']);
    }

    public function testSessionRules(): void
    {
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        $this->assertEquals('1', ini_get('session.cookie_secure'));
        $this->assertEquals('1', ini_get('session.cookie_httponly'));
        $this->assertEquals('1', ini_get('session.use_only_cookies'));
    }

    public function testEncryption(): void
    {
        $data = "securedata";
        $key = 'encryptionkey123';
        $encrypted = openssl_encrypt($data, 'AES-128-ECB', $key);
        $this->assertNotEquals($data, $encrypted);
        $decrypted = openssl_decrypt($encrypted, 'AES-128-ECB', $key);
        $this->assertEquals($data, $decrypted);
    }

    public function testAccessPermissions(): void
    {
        $roles = ['manager', 'staff', 'guest'];
        $permissions = [
            'manager' => ['view', 'edit', 'delete'],
            'staff' => ['view', 'edit'],
            'guest' => ['view'],
        ];
        $role = 'staff';
        $action = 'delete';
        $this->assertFalse(in_array($action, $permissions[$role]));
    }

    public function testAuditLog(): void
    {
        $log = [];
        $uid = 42;
        $activity = 'view';
        $item = 'fileA.txt';
        $log[] = [
            'user_id' => $uid,
            'action' => $activity,
            'resource' => $item,
            'timestamp' => time(),
        ];
        $this->assertCount(1, $log);
        $this->assertEquals($uid, $log[0]['user_id']);
        $this->assertEquals($activity, $log[0]['action']);
        $this->assertEquals($item, $log[0]['resource']);
    }
}
