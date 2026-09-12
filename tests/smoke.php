<?php
// CLI only. Fixtures and session editing never expose a test endpoint to the web.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../user.inc.php';
require_once __DIR__ . '/../adventure.inc.php';
set_error_handler(static function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
$checks = 0;
function check($condition, string $message): void {
    global $checks;
    if (!$condition) throw new RuntimeException("FAIL: $message");
    $checks++;
}
function scalar(string $sql, array $params = []): mixed { return db_query($sql, $params)->fetchColumn(); }
function player(string $name = 'smoke'): array { return db_query('SELECT gold,hp,experience,location,turns FROM users WHERE username = ?', [$name])->fetch(); }
function rejectedSql(string $sql, array $params = []): void {
    try { db_query($sql, $params); } catch (PDOException $error) {
        check($error->getCode() === '23000', 'foreign key or check constraint enforced'); return;
    }
    throw new RuntimeException('Invalid relational data was accepted');
}
class Browser {
    public string $cookie = '';
    public int $status = 0;
    public string $headers = '';
    function request(string $path, ?array $data = null): string {
        $socket = fsockopen('web', 80, $errno, $errstr, 10);
        if (!$socket) throw new RuntimeException($errstr);
        stream_set_timeout($socket, 20);
        $body = $data === null ? '' : http_build_query($data);
        $request = ($data === null ? 'GET' : 'POST') . " /$path HTTP/1.0\r\nHost: web\r\nConnection: close\r\n";
        if ($this->cookie !== '') $request .= "Cookie: PHPSESSID={$this->cookie}\r\n";
        if ($data !== null) $request .= "Content-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($body) . "\r\n";
        $request .= "\r\n$body";
        while ($request !== '') {
            $written = fwrite($socket, $request);
            if (!$written) throw new RuntimeException('HTTP write failed');
            $request = substr($request, $written);
        }
        $response = stream_get_contents($socket);
        $metadata = stream_get_meta_data($socket);
        fclose($socket);
        check(!$metadata['timed_out'], "HTTP completed: $path");
        [$this->headers, $body] = explode("\r\n\r\n", $response, 2);
        preg_match('/^HTTP\/\d\.\d (\d+)/', $this->headers, $status);
        $this->status = (int)$status[1];
        if (preg_match('/Set-Cookie: PHPSESSID=([^;\r\n]+)/i', $this->headers, $cookie)) $this->cookie = $cookie[1];
        check($this->status < 500, "No server error: $path");
        check(!preg_match('/Warning:|Deprecated:|Fatal error:|Notice:|SQLSTATE/', $body), "No PHP/SQL diagnostics: $path");
        return $body;
    }
    function login(string $name, string $password): void {
        $this->request('checkPassword.php', ['username' => $name, 'password' => $password]);
        check($this->status === 302 && str_contains($this->headers, 'Location: userProfile.php'), 'login redirect');
    }
}
function editSession(Browser $browser, callable $edit): void {
    check($browser->cookie !== '', 'session cookie exists');
    session_id($browser->cookie);
    session_start();
    $edit();
    session_write_close();
    $_SESSION = [];
}
function fixture(Browser $browser, string $kind): void {
    editSession($browser, static function () use ($kind) {
        $a = new adventure('The_Blue_River', 6);
        if ($kind === 'combat') {
            $a->type = 2;
            $a->completed = false;
            $a->monster = ['monstername' => 'Goblin', 'hp' => 7, 'str' => 0, 'dex' => -20];
            $a->gold = 7; $a->experience = 11; $a->item = 2;
        } else {
            $a->type = 1;
            $a->newAdventure(1);
        }
        $_SESSION['adventure'] = serialize($a);
    });
}
check(PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 5, 'PHP 8.5 runtime');
check(str_starts_with(scalar('SELECT VERSION()'), '8.4.'), 'MySQL 8.4 runtime');
check(str_contains(scalar('SELECT @@sql_mode'), 'STRICT_TRANS_TABLES'), 'strict SQL defaults');
check(scalar('SELECT @@foreign_key_checks') == 1, 'foreign keys enabled');
check(db_query('SELECT name,location,level FROM places ORDER BY location')->fetchAll() === [
    ['name'=>'Town','location'=>1,'level'=>1], ['name'=>'The Blue River','location'=>2,'level'=>1],
    ['name'=>'The Dark Forest','location'=>3,'level'=>5], ['name'=>'The Deep Cave','location'=>4,'level'=>10]
], 'exact location seeds');
check(db_query('SELECT * FROM items ORDER BY itemID')->fetchAll() === [
    ['itemID'=>1,'itemname'=>'Steel Sword','itemtype'=>1,'isWearable'=>1,'strAdded'=>5,'dexAdded'=>1,'hpAdded'=>0],
    ['itemID'=>2,'itemname'=>'Potion','itemtype'=>0,'isWearable'=>0,'strAdded'=>0,'dexAdded'=>0,'hpAdded'=>10],
    ['itemID'=>3,'itemname'=>'Big Potion','itemtype'=>0,'isWearable'=>0,'strAdded'=>0,'dexAdded'=>0,'hpAdded'=>50],
    ['itemID'=>4,'itemname'=>'Sheild','itemtype'=>4,'isWearable'=>1,'strAdded'=>0,'dexAdded'=>5,'hpAdded'=>5]
], 'exact item seeds');
$monsterRows = db_query('SELECT monstername,level,location,hp,str,dex,gold,item,itemChance FROM monsters ORDER BY monstername')->fetchAll(PDO::FETCH_NUM);
check($monsterRows === [
    ['Big Goblin',3,2,3,4,6,5,2,0], ['Cave Bot',10,4,90,30,25,1000,3,0],
    ['Fire Bot',10,4,90,30,25,1000,3,0], ['Forest Bot',8,3,40,14,10,60,3,0],
    ['Forest Drake',6,3,10,5,18,15,1,0], ['Goblin',1,2,2,2,3,1,2,0]
], 'exact monster seeds including 1000 gold');
check(scalar('SELECT COUNT(*) FROM adventures') === 0 && scalar('SELECT COUNT(*) FROM quests') === 0, 'no invented encounter/quest seeds');
$empty = new adventure('Town', 6);
check($empty->isCompleted() && $empty->getType() === 0, 'empty location completes safely');
check(str_contains($empty->showAdventure(), 'nothing to encounter'), 'empty encounter message rendered');
check($empty->getReward() === ['experience'=>0, 'gold'=>0, 'item'=>0], 'empty encounter has no rewards');
check(db_query('SELECT owner,itemtype,qty FROM inventory ORDER BY owner,itemtype')->fetchAll(PDO::FETCH_NUM) === [
    ['bob',1,1],['bob',2,1],['dill',1,1],['dill',2,1],['dill',3,1]
], 'all valid useful inventory seeds');
check(password_verify(getenv('DEV_PASSWORD'), scalar('SELECT password FROM administrators WHERE username = ?', ['admin'])), 'usable administrator hash');
rejectedSql('INSERT INTO inventory (owner,itemtype,qty) VALUES (?,1,1)', ['missing']);
rejectedSql('INSERT INTO inventory (owner,itemtype,qty) VALUES (?,999,1)', ['dill']);
rejectedSql('INSERT INTO quests (itemToComplete) VALUES (999)');
rejectedSql('UPDATE users SET location=99 WHERE username=?', ['dill']);
db_query('INSERT INTO quests (name,itemToComplete) VALUES (?,1)', ['Test quest']);
check(scalar('SELECT itemToComplete FROM quests') === 1, 'quest references item ID');
$dbUserCount = scalar('SELECT COUNT(*) FROM users');
$b = new Browser();
$b->request('userProfile.php');
check($b->status === 302 && str_contains($b->headers, 'Location: login.php'), 'anonymous access denied');
$anonymousCookie = $b->cookie;
$b->request('login.php'); check($b->status === 200, 'anonymous login page works');
$registration = ['username'=>'smoke','password'=>'smoke-password','verify_password'=>'smoke-password','email'=>'smoke@example.test','class'=>'1'];
$b->request('newAccount.php', $registration);
check($b->status === 302, 'registration succeeds');
check($b->cookie !== $anonymousCookie, 'registration rotates session ID');
check(scalar('SELECT COUNT(*) FROM users') === $dbUserCount + 1, 'one account inserted');
check(password_verify('smoke-password', scalar('SELECT password FROM users WHERE username=?', ['smoke'])), 'registration hashes password');
check(str_contains($b->request('userProfile.php'), "smoke's Profile"), 'registration session persists');
$other = new Browser();
$other->request('newAccount.php', $registration);
check($other->status === 422, 'duplicate registration rejected');
foreach ([['username'=>"bad' OR 1=1"], ['password'=>'short','verify_password'=>'short'], ['password'=>"password\0bad",'verify_password'=>"password\0bad"], ['password'=>str_repeat('a',73),'verify_password'=>str_repeat('a',73)], ['class'=>'9'], ['email'=>'bad'], ['verify_password'=>'different']] as $invalid) {
    $other->request('newAccount.php', array_replace($registration, ['username'=>'invalid'], $invalid));
    check($other->status === 422, 'invalid registration rejected');
}
check(scalar('SELECT COUNT(*) FROM users') === $dbUserCount + 1, 'invalid registrations leave database unchanged');
foreach ([['smoke','wrong-password'], ["' OR 1=1 -- ",'smoke-password']] as [$name,$password]) {
    $body = $other->request('checkPassword.php', ['username'=>$name,'password'=>$password]);
    check($other->status === 401 && !str_contains($body, $password), 'failed/injection login denied without password disclosure');
}
foreach (['dill','bob','admin'] as $name) {
    $seed = new Browser(); $seed->login($name, getenv('DEV_PASSWORD'));
    check(str_contains($seed->request('userProfile.php'), "$name's Profile"), 'seeded account works');
}
// Movement consumes one turn on encounter creation, not on subsequent combat requests.
$before = player();
$encounterBody = $b->request('TheBlueRiver.php');
check(str_contains($encounterBody, 'glorious battle') && str_contains($encounterBody, 'images/monsters/'), 'monster description and image rendered');
check(player()['location'] === 2 && player()['turns'] === $before['turns'] - 1, 'movement persisted');
$b->request('TheBlueRiver.php');
check(player()['turns'] === $before['turns'] - 1, 'encounter survives requests without another turn');
$b->request('TheDarkForest.php');
check($b->status === 302 && str_contains($b->headers, 'Location: TheBlueRiver.php'), 'active encounter stays at its location');
editSession($b, static function () { unset($_SESSION['adventure']); });
db_query('UPDATE users SET turns=0 WHERE username=?', ['smoke']);
$before = player(); $b->request('TheDarkForest.php');
check($b->status === 409 && player() === $before, 'zero turns rejects movement without changes');
$b->request('Town.php');
check($b->status === 409 && player() === $before, 'zero turns rejects Town movement');
$b->request('Town.php?task=rest');
check($b->status === 409 && player() === $before, 'zero turns rejects rest');
db_query('UPDATE users SET turns=30,hp=12 WHERE username=?', ['smoke']);
// Seed a normal serializable adventure in the private session store. No public test bypass.
fixture($b, 'combat');
$before = player();
$b->request('TheBlueRiver.php', ['adventurecommand'=>'Attack']);
check(player()['gold'] === $before['gold'] && player()['experience'] === $before['experience'], 'no premature combat rewards');
editSession($b, static function () {
    $a = unserialize($_SESSION['adventure']);
    check($a->monster['hp'] == 1, 'monster damage survives first combat request');
});
$b->request('TheBlueRiver.php', ['adventurecommand'=>'Attack']);
check(player()['gold'] === $before['gold'] + 7 && player()['experience'] === $before['experience'] + 11, 'combat rewards persisted exactly');
check(player()['turns'] === $before['turns'], 'combat did not consume extra turns');
check(scalar('SELECT SUM(qty) FROM inventory WHERE owner=? AND itemtype=2', ['smoke']) == 1, 'combat item awarded');
editSession($b, static function () { check(!isset($_SESSION['adventure']), 'completed encounter cleared'); });
db_query('INSERT INTO adventures (name,description,location,testAgainst,testValue,completeText,failText,rewardItemID) VALUES (?,?,2,?,0,?,?,2)',
    ['Test encounter','Test description','str','Test success','Test failure']);
fixture($b, 'success'); $before = player();
check(str_contains($b->request('TheBlueRiver.php'), 'Test success'), 'encounter success rendered');
check(player()['experience'] === $before['experience'] + 10, 'encounter experience persists');
check(scalar('SELECT SUM(qty) FROM inventory WHERE owner=? AND itemtype=2', ['smoke']) == 2, 'encounter item stacks');
db_query('UPDATE adventures SET testValue=999');
fixture($b, 'failure'); $before = player();
check(str_contains($b->request('TheBlueRiver.php'), 'Test failure'), 'encounter failure rendered');
check(player() === $before, 'failed encounter gives no rewards');
check(scalar('SELECT SUM(qty) FROM inventory WHERE owner=? AND itemtype=2', ['smoke']) == 2, 'failed encounter gives no item');
// Shop and inventory checks use both session-backed HTTP and direct database assertions.
db_query('UPDATE users SET gold=48 WHERE username=?', ['smoke']);
$before = player(); $b->request('shop.php?task=buy&itemid=1');
check(player() === $before && scalar('SELECT COUNT(*) FROM inventory WHERE owner=? AND itemtype=1', ['smoke']) === 0, 'insufficient funds rejected');
db_query('UPDATE users SET gold=49 WHERE username=?', ['smoke']);
// Sword price is round((15+3+25)*1.15) = 49, including exact-funds purchases.
$b->request('shop.php?task=buy&itemid=1');
check(player()['gold'] === 0, 'exact funds purchase debited correct wearable price');
$sword = scalar('SELECT invID FROM inventory WHERE owner=? AND itemtype=1', ['smoke']);
check($sword !== false, 'purchased sword persisted');
check(str_contains($b->request('userProfile.php', ['inventory'=>'all']), 'Steel Sword'), 'inventory display');
$b->request("userProfile.php?task=equip&itemid=$sword");
check(scalar('SELECT isEquipped FROM inventory WHERE invID=?', [$sword]) === 1, 'equip persisted');
check(str_contains($b->request('userProfile.php'), '<td>11</td>'), 'equipped strength reflected in session');
$b->request("userProfile.php?task=equip&itemid=$sword");
check(scalar('SELECT isEquipped FROM inventory WHERE invID=?', [$sword]) === 1, 'repeated equip stays equipped');
$b->request("userProfile.php?task=unequip&itemid=$sword");
check(scalar('SELECT isEquipped FROM inventory WHERE invID=?', [$sword]) === 0, 'unequip persisted');
$potion = scalar('SELECT invID FROM inventory WHERE owner=? AND itemtype=2', ['smoke']);
db_query('UPDATE users SET hp=1 WHERE username=?', ['smoke']);
$b->request("userProfile.php?task=usePotion&itemid=$potion");
check(player()['hp'] === 11 && scalar('SELECT qty FROM inventory WHERE invID=?', [$potion]) === 1, 'potion consumes one and heals');
$b->request("userProfile.php?task=usePotion&itemid=$potion");
check(player()['hp'] === 12 && scalar('SELECT qty FROM inventory WHERE invID=?', [$potion]) === 0, 'potion clamps HP and exhausts quantity');
$before = player(); $b->request("userProfile.php?task=usePotion&itemid=$potion");
check(player() === $before && scalar('SELECT qty FROM inventory WHERE invID=?', [$potion]) === 0, 'exhausted potion cannot heal again');
$b->request("userProfile.php?task=equip&itemid=$sword");
$b->request("shop.php?task=sell&itemid=$sword");
check(player()['gold'] === 17 && scalar('SELECT qty FROM inventory WHERE invID=?', [$sword]) === 0 && scalar('SELECT isEquipped FROM inventory WHERE invID=?', [$sword]) === 0, 'sale credits exactly once and clears equipment');
check(!str_contains($b->request('userProfile.php', ['inventory'=>'all']), '1x Steel Sword'), 'sold item removed from inventory display');
$before = player(); $b->request("shop.php?task=sell&itemid=$sword");
check(player() === $before, 'exhausted sale rejected');
$foreign = scalar('SELECT invID FROM inventory WHERE owner=? AND itemtype=1', ['bob']);
foreach ([$foreign, 999999, $sword] as $id) {
    foreach (['equip','unequip','usePotion','sell'] as $task) {
        $before = player();
        $inventory = db_query('SELECT * FROM inventory ORDER BY invID')->fetchAll();
        $b->request("userProfile.php?task=$task&itemid=$id");
        check(player() === $before && db_query('SELECT * FROM inventory ORDER BY invID')->fetchAll() === $inventory, 'unowned/missing/exhausted action leaves all state unchanged');
    }
}
$before = player(); $b->request('shop.php?task=buy&itemid=999999');
check(player() === $before, 'missing template rejected');
$b->request('shop.php?task=buy&itemid=1%20OR%201=1');
check($b->status === 400 && player() === $before, 'invalid item input rejected');
fixture($b, 'combat');
$oldCookie = $b->cookie;
$b->request('userProfile.php?task=logout');
check($b->status === 302, 'logout redirects');
$b->request('userProfile.php');
check($b->status === 302 && str_contains($b->headers, 'Location: login.php'), 'logout denies access');
$b->cookie = $oldCookie;
$b->request('TheBlueRiver.php');
check($b->status === 302 && str_contains($b->headers, 'Location: login.php'), 'old session cannot resume adventure');
$b->login('smoke', 'smoke-password');
check($b->cookie !== $oldCookie, 'login regenerates session ID');
editSession($b, static function () { check(!isset($_SESSION['adventure']), 'logout/login clears adventure state'); });
db_query('UPDATE users SET hp=1,turns=10,location=2 WHERE username=?', ['smoke']);
$b->request('Town.php?task=rest');
check($b->status === 302 && player()['location'] === 1 && player()['turns'] === 9 && player()['hp'] === 7, 'rest moves, heals and consumes exactly one turn');
$before = player();
$b->request('Town.php');
check(player() === $before, 'Town refresh consumes no extra turn');
foreach (['index.php','mainMap.php','Town.php','shop.php','userProfile.php'] as $page) {
    $b->request($page); check($b->status === 200, "page works: $page");
}
foreach (['style.css','images/map/TheBlueRiver.gif'] as $asset) {
    $b->request($asset); check($b->status === 200, "asset preserved: $asset");
}
$b->request('conn.inc'); check($b->status === 403, 'include source not served');
echo "PASS: $checks database and HTTP assertions.\n";
