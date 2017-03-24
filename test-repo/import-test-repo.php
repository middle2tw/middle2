<?php

include(__DIR__ . '/../webdata/init.inc.php');

// add user test@middle2
try {
    $user = User::insert(array('name' => 'test@middle2'));
} catch (Pix_Table_DuplicateException $e) {
    $user = User::find_by_name('test@middle2');
}

// generate key-pair for test@middle2
if (!file_exists('git-key')) {
    system("ssh-keygen -f git-key -N ''");
}
$keybody = file_get_contents('git-key.pub');
$keybody = trim($keybody);
$terms = explode(' ', $keybody);
if (3 !== count($terms)) {
    throw new InvalidArgumentException('invalid key');
}
list($type, $body, $user_host) = $terms;
if (!in_array($type, array('ssh-rsa', 'ssh-dsa'))) {
    throw new InvalidArgumentException('invalid ssh type');
}

if (preg_match('#[^a-zA-Z0-9/+=]#', $body)) {
    throw new InvalidArgumentException('invalid ssh key');
}

if (!$user->keys->search(array('key_fingerprint' => md5(base64_decode($body))))->first()) {
    $user->keys->insert(array(
        'key_fingerprint' => md5(base64_decode($body)),
        'key_body' => $keybody,
    ));
}

// import testing repository
system("rm -rf tmp_repo");
foreach (glob("test-*") as $test_path) {
    try {
        $project = $user->addProject($test_path);
    } catch (Pix_Table_DuplicateException $exception) {
        $project = Project::find_by_name($test_path);
        if (!$project->members->search(array('user_id' => $user->id))->first()) {
            throw new Exception("project '{$test_path}' is not owned by user 'test@middle2'");
        }
    }
    error_log("cloning {$test_path} to tmp_repo/");
    system("ssh-agent bash -c 'ssh-add git-key; git clone git@" . getenv('GIT_PUBLIC_SERVER') . ':' . $test_path . ' tmp_repo\'');

    system("cp -r {$test_path}/* tmp_repo");
    system("git -C tmp_repo/ add .");
    system("git -C tmp_repo/ commit -m 'update'");
    system("ssh-agent bash -c 'ssh-add git-key; git -C tmp_repo/ push -f'");
    system("rm -rf tmp_repo");
}
