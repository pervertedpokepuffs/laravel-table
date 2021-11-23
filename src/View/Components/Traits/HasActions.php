<?php

namespace Sysniq\LaravelTable\View\Components\Traits;

use Exception;

/**
 * Trait for action support.
 */
trait HasActions
{
    /**
     * Prefix to route.
     * 
     * @var string
     */
    public $routePrefix = null;

    /**
     * @var string
     */
    public $modelName;

    /**
     * All actions in the table,
     * 
     * @var array
     */
    public $actions = [];

    /**
     * Define action columns.
     * 
     * @return void
     */
    abstract protected function actionDefinition();

    /**
     * Define a create action.
     * 
     * @param array $options Default([])
     * @return $this
     */
    public function defineCreateAction(array $options = [])
    {
        array_push($this->actions, $this->makeAction('create', $options));
        return $this;
    }

    /**
     * Define a show action.
     * 
     * @param array $options Default([])
     * @return $this
     */
    public function defineShowAction(array $options = [])
    {
        array_push($this->actions, $this->makeAction('show', $options));
        return $this;
    }

    /**
     * Define a edit action.
     * 
     * @param array $options Default([])
     * @return $this
     */
    public function defineEditAction(array $options = [])
    {
        array_push($this->actions, $this->makeAction('edit', $options));
        return $this;
    }

    /**
     * Define a delete action.
     * 
     * @param array $options Default([])
     * @return $this
     */
    public function defineDeleteAction(array $options = [])
    {
        array_push($this->actions, $this->makeAction('destroy', $options));
        return $this;
    }

    /**
     * Define a custom action based on view.
     * 
     * @param string $view
     * @param array $data Default([])
     * @return $this
     */
    public function defineCustomAction(string $view, array $data = [], array $options = [])
    {
        array_push($this->actions, $this->makeAction('custom', ['content' => ['view' => $view, 'data' => $data]]));
        return $this;
    }

    /**
     * Define download action.
     * 
     * @return $this
     */
    public function defineDownloadAction(array $options = [])
    {
        array_push($this->actions, $this->makeAction('download', $options));
        return $this;
    }

    /**
     * Build the temporary action.
     * 
     * @param array $options
     * @return array
     */
    private function makeAction(string $action, array $options = [])
    {
        if (!in_array($action, ['custom', 'download']) && $this->routePrefix == null) throw new Exception("Cannot create routes on this table. routePrefix is NULL.");
        $options = $options + ['class' => null, 'title' => ucwords("{$action} {$this->modelName}"), 'type' => $action, 'name' => null];
        if ($this->sourceAjax != null && $action != 'download' && $action != 'create') {
            if ($action != 'custom') {
                $route = route($options['route'] ?? "{$this->routePrefix}.{$action}", ['id' => 'objectid']);
                $options = $options + ['route' => $route] + ['content' => ['name' => 'id', 'view' => view("st::table-actions.ajax-{$action}-action", [
                    'route' => $route,
                    'class' => $options['class'],
                    'title' => $options['title'],
                ])->render()]];
            } elseif ($action == 'custom')
                $options['content'] = ['view' => view($options['content']['view'], $options['content']['data'])->render(), 'name' => 'id'];
        }
        $options = $options + ['route' => $options['route'] ?? "{$this->routePrefix}.{$action}"];
        return $options;
    }
}
