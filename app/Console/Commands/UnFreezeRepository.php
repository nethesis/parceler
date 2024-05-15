<?php

namespace App\Console\Commands;

use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use function Laravel\Prompts\select;

class UnFreezeRepository extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:unfreeze {repository}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unfreeze a frozen repository.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $repoName = $this->argument('repository');
        try {
            $repository = Repository::where('name', $repoName)->firstOrFail();
        } catch (ModelNotFoundException) {
            $this->error("Repository '$repoName' not found.");
            return self::FAILURE;
        }
        if ($repository->freeze == null) {
            $this->error("Repository '$repository->name' is not frozen.");
            return self::FAILURE;
        }
        $this->info("Unfreezing repository '$repository->name'...");
        $repository->freeze = null;
        $repository->save();
        return self::SUCCESS;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array<string, string>
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'repository' => fn () => select(
                label: 'Choose repository to unfreeze',
                options: Repository::pluck('name'),
                required: true,
            ),
        ];
    }
}
