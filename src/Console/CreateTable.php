<?php

namespace Sysniq\LaravelTable\Console;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class CreateTable extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:laraveltable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new laravel table component.';

    protected $type = '\Table';

    protected function getStub()
    {
        return __DIR__ . '/Stubs/BaseTable.php.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\View\Components';
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Creating a new LaravelTable...');
        parent::handle();
        $this->doOtherOperations();
        return 0;
    }

    protected function doOtherOperations()
    {
        $class = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($class);
        $content = file_get_contents($path);
        file_put_contents($path, $content);
    }
}
