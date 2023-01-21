<?php

declare(strict_types=1);

namespace Deployer;

require 'recipe/symfony.php';

set('ssh_multiplexing', false);
set('keep_releases', 2);

task('deploy:vendors', function () {
    run('cd {{release_or_current_path}} && composer install');
});

task('deploy:setup', function () {
    run('cd {{deploy_path}}');
    run('mkdir -p .dep');
    run('mkdir -p releases');
    run('mkdir -p shared');
});

task('deploy:npm', function () {
    run('cd {{release_or_current_path}} && npm install && npm run build');
});

// Config

set('repository', 'git@github.com:Proglab/verhulst-erp.git');

add('shared_files', []);
add('shared_dirs', ['var/files']);
add('writable_dirs', ['var/files']);

// Hosts

host('server51.insideweb.be')
    ->set('remote_user', 'friendssales2023')
    ->set('deploy_path', '~/sales.verhulst.pro');

// Hooks

after('deploy:failed', 'deploy:unlock');
after('deploy:symlink', 'deploy:npm');
after('database:migrate', 'deploy:vendors');
