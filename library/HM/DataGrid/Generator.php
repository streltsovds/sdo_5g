<?php
/**
 * Класс генерации общей файловой структуры для гридов типа HM_DataGrid
 *
 */

class HM_DataGrid_Generator
{
    private $path;
    private $name;
    private $table;
    private $bases = ['els', 'at', 'mobile', 'hr', 'recruit', 'tc' , 'cms', 'wrapper'];

    public function run()
    {
        $message = '';
        $options = [];
        $attribs = ['task', 'path', 'name', 'table'];

        foreach ($attribs as $attrib) {
            $option = getopt(null, ["{$attrib}:"]);
            if (isset($option[key($option)]) && $option[key($option)]) $options[key($option)] = $option[key($option)];
        }

        if (!isset($options['task'])) die('ERROR: Task not specified!');

        switch ($options['task']) {
            case 'generate':
            case 'g':
                $this->generate($options);
                $message = 'The generation is completed with success. Now you have to customize your fresh datagrid. Enjoy!';
                break;
            default:
                break;
        }

        echo $message;
    }

    private function generate($options)
    {
        $this->path  = isset($options['path' ]) ? $options['path' ] : die('ERROR: The path is not specified!' );
        $this->table = isset($options['table']) ? $options['table'] : die('ERROR: The table is not specified!');
        $this->name  = isset($options['name' ]) ? $options['name' ] : null;

        $pathParts   = $this->parsePath();
        $dataGridDir = __DIR__ . '/../../../application/modules/' .
            (isset($pathParts['base']) ? $pathParts['base'] . '/' : '') .
            $pathParts['module'] . '/data-grids';

        $templateFile = __DIR__ . '/../../../data/templates/datagrid/dataGridTemplate.dgt';

        $dataGridFile = (isset($this->name) ?
                ucfirst($pathParts['module'    ]) . $this->name :
                ucfirst($pathParts['module'    ]) .
                ucfirst($pathParts['controller']) .
                ucfirst($pathParts['action'    ])
        ) . 'DataGrid.php';

        if (!file_exists($dataGridDir)) mkdir($dataGridDir);
        if (!file_exists($dataGridDir . '/actions')) mkdir($dataGridDir . '/actions');
        if (!file_exists($dataGridDir . '/mass-actions')) mkdir($dataGridDir . '/mass-actions');
        if (!file_exists($dataGridDir . '/callbacks')) mkdir($dataGridDir . '/callbacks');
        if (!file_exists($dataGridDir . '/filters')) mkdir($dataGridDir . '/filters');

        if (!file_exists($dataGridDir . '/' . $dataGridFile)) touch($dataGridDir . '/' . $dataGridFile);
        if (!file_exists($dataGridDir . '/actions/.gitkeep')) touch($dataGridDir . '/actions/.gitkeep');
        if (!file_exists($dataGridDir . '/mass-actions/.gitkeep')) touch($dataGridDir . '/mass-actions/.gitkeep');
        if (!file_exists($dataGridDir . '/callbacks/.gitkeep')) touch($dataGridDir . '/callbacks/.gitkeep');
        if (!file_exists($dataGridDir . '/filters/.gitkeep')) touch($dataGridDir . '/filters/.gitkeep');

        $template = file_get_contents($templateFile);
        $template = str_replace('{{CLASSNAME}}', $this->generateClassName($pathParts), $template);
        $template = str_replace('{{TABLE}}', $this->table, $template);

        if ('' == file_get_contents($dataGridDir . '/' . $dataGridFile))
            file_put_contents($dataGridDir . '/' . $dataGridFile, $template);
    }

    private function generateClassName(array $pathParts)
    {
        $className = 'HM_{{MODULE}}_DataGrid_{{MODULE}}{{CONTROLLER}}{{ACTION}}DataGrid';
        $className = str_replace('{{MODULE}}', ucfirst($pathParts['module']), $className);
        if ($this->name) {
            $className = str_replace('{{CONTROLLER}}{{ACTION}}', $this->name, $className);
        } else {
            $className = str_replace('{{CONTROLLER}}', ucfirst($pathParts['controller']), $className);
            $className = str_replace('{{ACTION}}', ucfirst($pathParts['action']), $className);
        }

        return $className;
    }

    private function parsePath()
    {
        $base  = $module = $controller = $action = '';
        $exploded = explode('/', $this->path);
        $isBaseSpecified = in_array($exploded[0], $this->bases);
        if (count($exploded)  < 2) die('ERROR: The path is not correct!');
        if (count($exploded) == 2) {
            if ($isBaseSpecified)
                 list($base, $module)       = $exploded;
            else list($module, $controller) = $exploded;
        }
        if (count($exploded) == 3) {
            if ($isBaseSpecified)
                 list($base, $module, $controller)   = $exploded;
            else list($module, $controller, $action) = $exploded;
        }
        if (count($exploded) == 4) {
            if ($isBaseSpecified)
                 list($base, $module, $controller, $action) = $exploded;
            else list($module, $controller, $action,      ) = $exploded;
        }

        $result = ['base' => $base ?: 'els', 'module' => $module, 'controller' => $controller, 'action' => $action];

        return $result;
    }
}
