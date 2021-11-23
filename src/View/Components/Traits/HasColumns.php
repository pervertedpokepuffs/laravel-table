<?php

namespace Sysniq\LaravelTable\View\Components\Traits;

use Exception;
use Illuminate\Support\Collection;

/**
 * Trait for column support.
 */
trait HasColumns
{
    /**
     * @var array
     */
    public $columns = [];

    /**
     * @var bool
     */
    public $isIndexed = false;

    /**
     * Define the columns in the table.
     * 
     * @return void
     */
    abstract protected function columnDefinition();

    /**
     * Index column
     * 
     * @param bool $setIndex
     * @param array $options Default=[]
     * @return object $this
     */
    public function defineIndexColumn(bool $setIndex = true, array $options = [])
    {
        $options = $options + ['priority' => null, 'class' => null, 'header' => null];
        $options['class'] = $options['class'] ?? 'index';
        $this->isIndexed = $setIndex;
        if ($setIndex) {
            array_unshift($this->columns, $this->makeColumn('index', 'index', $options, 'No.'));
        } else {
            $first_col = $this->columns[0];
            if ($first_col['content'] == 'index') array_shift($this->columns);
        }
        return $this;
    }


    /**
     * Column based on a model column.
     * 
     * @param string $key Key to access the model data with. Traverse the model by chaining '.'.  Use '()' to access first() in a relationship. 
     * @param array $options Default=[]
     * @return object $this
     */
    public function defineModelColumn(string $key, array $options = [])
    {
        array_push($this->columns, $this->makeColumn($key, 'model', $options, $key));
        return $this;
    }

    /**
     * Column based on a model and callback.
     * 
     * @param function $callback
     * @param $options Default=[]
     * @return object $this
     */
    public function defineCallbackColumn($callback, array $options = [])
    {
        array_push($this->columns, $this->makeColumn($callback, 'callback', $options));
        return $this;
    }

    /**
     * Column based on input HTML.
     * 
     * @param string $htmlString
     * @param array $options Default=[]
     * @return object $this
     */
    public function defineHtmlColumn(string $htmlString, array $options = [])
    {
        array_push($this->columns, $this->makeColumn($htmlString, 'html', $options));
        return $this;
    }

    /**
     * Column based on view and data input.
     * 
     * @param string $view
     * @param array $data
     * @param array $options Default=[]
     * @return object $this
     */
    public function defineViewColumn(string $view, array $data = [], array $options = [])
    {
        array_push($this->columns, $this->makeColumn(['view' => $view, 'data' => $data], 'view', $options, $view));
        return $this;
    }

    /**
     * Column based on an AJAX column.
     *
     * @param string $columnName
     * @param array $options
     * @param string $jsCallback
     * @return void
     */
    public function defineAjaxColumn(string $columnName, array $options = [], string $jsCallback = null)
    {
        array_push($this->columns, $this->makeColumn(['name' => $columnName, 'callback' => $jsCallback], 'ajax', $options));
        return $this;
    }

    /**
     * Build the temporary column.
     * 
     * @param string|array $content
     * @param array $options
     * @param string $defaultName Default=''
     * @return array
     */
    private function makeColumn($content, string $type, array $options, string $defaultName = '')
    {
        $options = $options + ['priority' => null, 'class' => null, 'header' => null];
        $column_tmp = [];
        $column_tmp['content'] = $content;
        $column_tmp = $column_tmp + $options;
        $column_tmp['header'] = $column_tmp['header'] ?? $defaultName;
        $column_tmp['type'] = $type;
        return $column_tmp;
    }

    /**
     * Traverse the model.
     * 
     * @param string|string[] $key
     * @param object|Collection|array $current_object
     * @return mixed
     */
    public function columnTraverse($keys, $current_object)
    {
        if (is_string($keys)) $keys = explode('.', $keys);
        // Return the current object if the current key has been fully traversed.
        if (count($keys) == 0) return $current_object;

        // Check if key is valid.
        if (!is_string($keys[0])) throw new Exception("Key is not a string or a string array.");

        // Convert collections to array.
        if ($current_object instanceof Collection) $current_object = $current_object->toArray();
        if (is_array($current_object)) $current_object = (object)$current_object;

        try {
            $current_key = array_shift($keys);
            $isFirst = strpos($current_key, '()') !== false;

            if ($isFirst) return $this->columnTraverse($keys, $current_object->$current_key()->first());
            return $this->columnTraverse($keys, $current_object->$current_key);
        } catch (Exception $e) {
            throw new Exception("Cannot access relationship {$keys[0]} of {implode($keys)}. {$e}");
        }
    }
}
