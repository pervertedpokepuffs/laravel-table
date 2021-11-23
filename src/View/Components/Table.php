<?php

namespace Sysniq\LaravelTable\View\Components;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Illuminate\Support\Collection;
use Sysniq\LaravelTable\View\Components\Traits\HasActions;
use Sysniq\LaravelTable\View\Components\Traits\HasColumns;

abstract class Table extends Component
{
    use HasColumns, HasActions;

    public $id;
    public $models;
    public $exportTitle;
    public $modelName;
    public $sourceAjax;

    /**
     * Create a new component instance.
     *
     * @param string $id
     * @param Collection|array $models
     * @param string $routePrefix
     * @param string $exportTitle
     * @return void
     */
    public function __construct(string $id, $sourceAjax = null, $models = null, string $routePrefix = null, string $exportTitle = null, $modelName = null)
    {
        if ($sourceAjax == null && $models == null) throw new Exception('Table must accept only one form of data, either via $models or $sourceAjax');

        if ($models != null) {
            if (!($models instanceof Collection) && !is_array($models)) throw new Exception("Table data must be type of array or Collection.");
            if (is_array($models)) collect($models);
            $this->models = $models ?? collect([]);
            if (count($models) > 0 && $models[0] instanceof Model) {
                if ($this->modelName == null) {
                    $this->modelName = explode('\\', get_class($models[0]));
                    $this->modelName = strtolower(array_pop($this->modelName));
                }
                $this->routePrefix = $this->routePrefix ?? strtolower("{$this->modelName}s");
            }
        }

        $this->id = $id;

        $this->modelName = $modelName ?? ($this->modelName ?? 'id');

        $this->sourceAjax = $sourceAjax;
        $this->exportTitle = $exportTitle;
        $this->routePrefix = $routePrefix ?? $this->routePrefix;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $this->columnDefinition();
        $this->actionDefinition();

        return view('st::components.table');
    }
}
