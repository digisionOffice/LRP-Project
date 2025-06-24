<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

/** A helper command for common Git actions.
 *
 * --- USAGE EXAMPLES ---
 * php artisan git add                     // Stages all new and modified files.
 * php artisan git push                    // Pushes committed changes to the remote.
 *
 * php artisan git commit                  // Commits staged files with the default message ('indrasb').
 * php artisan git commit "feat: Add new feature" // Commits with a custom message.
 *
 * php artisan git aio                     // Runs add, commit (default msg), and push in sequence.
 * php artisan git aio "fix: Hotfix for login bug" // Runs add, commit (custom msg), and push.
*/

class GitCommand extends Command
{
    /**
     * The name and signature of the console command.
     * We define 'action' as the first argument.
     * We make 'message' optional with a '?' and set its default in the code.
     */
    protected $signature = 'git {action : The git action to perform (add, commit, push, aio)}
                                {message? : The commit message}';

    /**
     * The console command description.
     */
    protected $description = 'A helper command to perform common Git actions.';

    /**
     * Execute the console command. Acts as a dispatcher based on the action.
     */
    public function handle(): int
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'add':
                return $this->handle_add();

            case 'commit':
                return $this->handle_commit();

            case 'push':
                return $this->handle_push();

            case 'aio': // All-In-One
                return $this->handle_aio();

            default:
                $this->error("Invalid action '{$action}'. Available actions are: add, commit, push, aio.");
                return self::FAILURE;
        }
    }

    /**
     * Handles the 'git add' action.
     */
    private function handle_add(): int
    {
        $this->info('Staging all changes (git add .)');
        return $this->runProcess('git add .') ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Handles the 'git commit' action.
     */
    private function handle_commit(): int
    {
        // Use the provided message or the default 'indrasb'
        $commitMessage = $this->argument('message') ?? 'indrasb';

        $this->info("Committing with message: \"{$commitMessage}\"");
        $commitCommand = 'git commit -m "' . addslashes($commitMessage) . '"';

        // The second parameter tells runProcess to ignore "nothing to commit" errors
        return $this->runProcess($commitCommand, true) ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Handles the 'git push' action.
     */
    private function handle_push(): int
    {
        $this->info('Pushing to remote repository...');
        return $this->runProcess('git push') ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Handles the 'all-in-one' action.
     */
    private function handle_aio(): int
    {
        $this->comment('--- Starting All-In-One Git Process ---');

        $this->comment("\nStep 1/3: Staging files...");
        if ($this->handle_add() === self::FAILURE) {
            return self::FAILURE;
        }

        $this->comment("\nStep 2/3: Committing changes...");
        if ($this->handle_commit() === self::FAILURE) {
            return self::FAILURE;
        }

        $this->comment("\nStep 3/3: Pushing to remote...");
        if ($this->handle_push() === self::FAILURE) {
            return self::FAILURE;
        }

        $this->info("\n✅ All-In-One process completed successfully!");
        return self::SUCCESS;
    }

    /**
     * Helper function to run a process and display its output.
     */
    private function runProcess(string $command, bool $ignoreEmptyCommitError = false): bool
    {
        $process = Process::timeout(120)->run($command);

        if ($process->successful()) {
            $this->line(trim($process->output()));
            return true;
        }

        $errorOutput = $process->errorOutput();

        if ($ignoreEmptyCommitError && str_contains($errorOutput, 'nothing to commit')) {
            $this->warn('Nothing to commit, working tree clean.');
            return true;
        }

        $this->error("The command `{$command}` failed.");
        $this->error("Error Output:\n" . $errorOutput);
        return false;
    }
}
