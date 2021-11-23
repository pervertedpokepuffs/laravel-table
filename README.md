# laravel-table

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Travis](https://img.shields.io/travis/sysniq/laravel-table.svg?style=flat-square)]()
[![Total Downloads](https://img.shields.io/packagist/dt/sysniq/laravel-table.svg?style=flat-square)](https://packagist.org/packages/sysniq/laravel-table)


## Install

```bash
composer require sysniq/laravel-table
```

Make sure that JQuery and AlpineJS is installed loaded in the page.


## Usage
In app.js
```javascript
import LaravelTable from 'path/to/published/vendor/sysniq/js/laravel-table'
```

In app.css
```css
@import 'path/to/published/vendor/sysniq/css/laravel-table'
```

All defined elements has access to the $model attribute for retrieving data from each row.

In page.blade.php
```html
{{-- Required fields --}}
<x-custom-table id="example-table" :models="$models" />
{{-- With all optionals using models --}}
<x-custom-table id="example-table" :models="$models" route-prefix="custom-prefix" export-title="Example Table Title" />
{{-- With all optionals using ajax --}}
<x-custom-table id="example-table-ajax" :source-ajax="route('ajax-route')" route-prefix="custom-prefix" export-title="Example Table Title" />

```
In App\Views\Components\CustomTable
```php
use Sysniq\LaravelTable\View\Components\Table;

class CustomTable extends Table
{
    protected function columnDefinition()
    {
        $options = ['header' => 'Custom Header', 'class' => 'custom-class', 'priority' => 1, 'sortable' => true, 'searchable' => true, 'date_range_filter' => true]; // Optionals that can be passed to column definitions.
        $this->defineIndexColumn(true); // Set the index column.
        $this->defineIndexColumn(true, $options); // Set a column with optionals.
        $this->defineModelColumn('column_name'); // Set a model column.
        $this->defineCallbackColumn(fn ($model) => null); // Set a callback column.
        $this->defineHtmlColumn('<b>This is a html string</b>'); // Set a HTML column.
        $this->defineViewColumn('example.view1', ['foo' => 'bar']); // Set a column from to render a view.
        $this->defineAjaxColumn('column_name'); // Set a column to show a data from an ajax response.
        $jsCallback = <<<js
        console.log(type) // Type is the whatever type the column is set to.
        console.log(row) // Row is all the values of that row.
        console.log(data) // Data is the value from the ajax.
        return data.toUpperCase()
        js
        $this->defineAjaxColumn('column_name', [], $jsCallback); // Set a column to show return from a callback of a row from an ajax response.
    }

    protected function actionDefinition()
    {
        $options = ['name' => 'custom-name', 'class' => 'custom-class', 'route' => 'custom.route', 'title' => 'Custom Title'] // Optionals that can be passed to action definitions.
        $this->defineCreateAction(); // Set the create action.
        $this->defineCreateAction($options); // Set an action with optionals.
        $this->defineShowAction(); // Set the show action.
        $this->defineEditAction(); // Set the edit action.
        $this->defineDeleteAction(); // Set the delete action.
        $this->defineDownloadAction(); // Set the download action.
        $this->defineCustomAction('custom.view', ['foo' => 'bar']); // Set a custom action with data.
    }
}
```

By default, all generic CRUD actions will use the default names for generated resource controller ('.create', '.show', '.edit', '.delete').

By default, all columns will not be sortable or searchable.

### Filtering
You can trigger a filtering action by dispatching an `ltfilter` event like below:
```javascript
const table = document.getElementById('table-id')
let filter_object = {
    filters: [
        { column: 0, value: 'Lorem' }, // The column number is the index of the column you wish to filter, so for example, 0 is the leftmost column.
        { column: 1, value: 'Ipsum' },
        ...
    ],
}

const event = new CustomEvent('ltfilter', { detail: filter_object })
table.dispatchEvent(event)
```

### Custom ajax actions
Strings of `objectid` will be replaced with the `id` value of the row. For example:
```html
<a href="www.host.com/users/objectid">User objectid</a>

<!-- Let's say that the id is 1 -->
<!-- The above element will effectively be changed to: -->
<a href="www.host.com/users/1">User 1</a>
```

## Testing

Run the tests with:

```bash
vendor/bin/phpunit
```


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Security

If you discover any security-related issues, please email aaadlima97@gmail.com instead of using the issue tracker.


## License

Copyright (C) SYSNIQ SDN. BHD. - All Rights Reserved

Unauthorized copying of this file, via any medium is strictly prohibited

Proprietary and confidential

Written by Ahmad Amirul 'Adli bin Mat Ali <aaadlima97@gmail.com>, August 2021.