<?php

declare(strict_types=1);

namespace Deployer;

use Deployer\Exception\ConfigurationException;

require 'recipe/symfony.php';

set('ssh_multiplexing', false);
set('keep_releases', 2);

task('deploy:vendors', function () {
    run('cd {{release_or_current_path}} && composer install --no-interaction --no-progress --optimize-autoloader');
});

task('deploy:setup', function () {
    run('cd {{deploy_path}} && mkdir -p .dep');
    run('cd {{deploy_path}} && mkdir -p releases');
    run('cd {{deploy_path}} && mkdir -p shared/public/files/products');
});

task('deploy:npm', function () {
    run('cd {{release_or_current_path}} && npm install');
    run('cd {{release_or_current_path}} && npm run build');
});

desc('Updates code');
task('deploy:update_code', function () {
    $git = get('bin/git');
    $repository = get('repository');
    $target = get('target');

    $targetWithDir = $target;
    if (!empty(get('sub_directory'))) {
        $targetWithDir .= ':{{sub_directory}}';
    }

    $bare = parse('{{deploy_path}}/.dep/repo');
    $env = [
        'GIT_TERMINAL_PROMPT' => '0',
        'GIT_SSH_COMMAND' => get('git_ssh_command'),
    ];

    start:
    // Clone the repository to a bare repo.
    run("[ -d $bare ] || mkdir -p $bare");
    run("[ -f $bare/HEAD ] || $git clone --mirror $repository $bare 2>&1", ['env' => $env]);

    cd($bare);

    // If remote url changed, drop `.dep/repo` and reinstall.
    if (run("$git config --get remote.origin.url") !== $repository) {
        cd('{{deploy_path}}');
        run("rm -rf $bare");
        goto start;
    }

    run("$git remote update 2>&1", ['env' => $env]);

    // Copy to release_path.
    if ('archive' === get('update_code_strategy')) {
        run("$git archive $targetWithDir | tar -x -f - -C {{release_path}} 2>&1");
    } elseif ('clone' === get('update_code_strategy')) {
        cd('{{release_path}}');
        run("$git clone -l $bare .");
        run("$git checkout --force $target");
    } else {
        throw new ConfigurationException(parse('Unknown `update_code_strategy` option: {{update_code_strategy}}.'));
    }

    // Save git revision in REVISION file.
    $rev = escapeshellarg(run("$git rev-list $target -1"));
    run("echo $rev > {{release_path}}/REVISION");

    // tag into .env
    $tag = run("$git describe --abbrev=0 --tags");
    run('echo "LATEST_TAG=' . $tag . '" > {{release_path}}/.env');
});

// Config

set('repository', 'git@github.com:Proglab/verhulst-erp.git');

add('shared_files', []);
add('shared_dirs', ['var/files', 'public/files/products']);
add('writable_dirs', ['var/files', 'public/files/products']);

// Hosts

host('server51.insideweb.be')
    ->set('remote_user', 'friendssales2023')
    ->set('deploy_path', '~/sales.verhulst.pro');

// Hooks

after('deploy:failed', 'deploy:unlock');
after('deploy:vendors', 'deploy:npm');
after('deploy:npm', 'database:migrate');
