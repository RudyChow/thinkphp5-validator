<?php
/**
 * Created by PhpStorm.
 * User: rudy
 * Date: 18-2-5
 * Time: 上午10:43
 */

namespace thinkphp5\validator;


use think\facade\Config;
use think\facade\Env;
use think\facade\View;
use think\Db;

class BuilderHelper
{
    private $origin_db = '';
    private $prefix = '';
    private $connect = null;
    private $output_path = '';

    public function __construct()
    {
        $config = Config::pull('database');
        $this->origin_db = $config['database'];
        $this->prefix = $config['prefix'];
        $this->output_path = Env::get('app_path') . 'common' . DIRECTORY_SEPARATOR . 'validate' . DIRECTORY_SEPARATOR;
        $config['database'] = 'INFORMATION_SCHEMA';
        $this->connect = Db::connect($config);
    }

    /**
     * @author: Rudy
     * @time: 2018年2月5日
     * description:获取所有数据表
     * @return array
     */
    public function getTables()
    {
        $validators = $this->getAllValidator();
        $result = $this->connect->table('TABLES')->field(['TABLE_NAME as table_name', 'TABLE_COMMENT as table_comment'])->where('TABLE_SCHEMA', $this->origin_db)->select();
        //添加是否存在验证器的属性并且排序,不存在的在先
        if (!empty($result)) {

            foreach ($result as $key => &$v) {
                $exist[$key] = $v['is_exist'] = in_array($v['table_name'], $validators) ? 1 : 0;
            }
            array_multisort($exist, SORT_ASC, $result);
        }

        return $result;
    }

    /**
     * @author: Rudy
     * @time: 2018年2月5日
     * description:
     * @param $tables
     * @param $override
     * @return array
     */
    public function generate($tables, $override = 0)
    {
        $tables_data = [];
        $result = $this->connect->table('COLUMNS')->field([
            'COLUMN_NAME as col_name', 'COLUMN_TYPE as col_type', 'DATA_TYPE as data_type', 'IS_NULLABLE is_null',
            'COLUMN_DEFAULT as col_default', 'CHARACTER_MAXIMUM_LENGTH as char_max_len', 'NUMERIC_PRECISION as num_len', 'NUMERIC_SCALE as num_scale',
            'COLUMN_COMMENT as col_comment', 'TABLE_NAME as table_name'])
            ->where(['TABLE_SCHEMA' => $this->origin_db])->whereIn('TABLE_NAME', $tables)->select();
        foreach ($result as $v) {
            $tables_data[$v['table_name']][] = $v;
        }


        /*
         * 如果文件夹不存在，则创建文件夹
         */
        if (!is_dir($this->output_path)) {
            $old = umask(0);
            mkdir($this->output_path, 0775, true);
            unset($old);
        }

        /**
         * 生成验证器
         */
        $view = new View();
        $tables = array_flip($tables);
        foreach ($tables_data as $table_name => $fields) {
            $params = [];
            //类名和文件名
            $params['validator'] = array_reduce(explode('_', str_replace($this->prefix, '', $table_name)), function ($carry, $item) {
                return ucfirst($carry) . ucfirst($item);
            });
            $file_name = $this->output_path . $params['validator'] . '.php';
            if (!$override && file_exists($file_name)) {
                $tables[$table_name] = 0;
                continue;
            }

            $params['time'] = date('Y-m-d H:i:s');
            foreach ($fields as $field) {
                //字段别名
                $params['fields'][$field['col_name']] = $field['col_comment'] ? $field['col_comment'] : $field['col_name'];
                //规则定义
                $rule = [];
                if ($field['is_null'] == 'NO') {
                    $rule[] = 'require';
                }
                switch ($field['data_type']) {
                    case 'tinyint':
                        $this->parseInt($rule, $field, pow(2, 8));
                        break;
                    case 'smallint':
                        $this->parseInt($rule, $field, pow(2, 16));
                        break;
                    case 'mediumint':
                        $this->parseInt($rule, $field, pow(2, 24));
                        break;
                    case 'int':
                        $this->parseInt($rule, $field, pow(2, 32));
                        break;
                    case 'bigint'://int
                        $this->parseInt($rule, $field, pow(2, 64));
                        break;
                    case 'float':
                    case 'double':
                    case 'decimal'://float
                        $this->parseDecimal($rule, $field);
                        break;
                    case 'char':
                    case 'varchar':
                    case 'tinytext':
                    case 'mediumtext':
                    case 'longtext':
                    case 'text'://string
                        $this->parseString($rule, $field);
                        break;
                    case 'enum'://enum
                        $this->parseEnum($rule, $field);
                        break;
                    case 'date':
                    case 'time':
                    case 'year':
                    case 'datetime':
                    case 'timestamp':
                        $this->parseDate($rule);
                        break;
                    default:
                        break;
                }
                $params['rules'][$field['col_name']] = implode('|', $rule);
            }
            $output = '<?php' . $view::fetch(VALIDATOR_TEMPLATE_PATH . 'validate.tpl', $params);

            if (file_put_contents($file_name, $output)) {
                $tables[$table_name] = 1;
                chmod($file_name, 0664);
            } else {
                $tables[$table_name] = 0;
            }
        }
        return $tables;
    }

    private function parseInt(&$rule, $field, $max)
    {
        $rule[] = 'integer';
//        if (strpos($field['data_type'], 'unsigned')) {
//            $rule[] = 'egt:0';
//        }
        $rule[] = strpos($field['data_type'], 'unsigned') !== false ? 'length:0,' . ($max - 1) : 'length:-' . ($half = $max / 2) . ',' . ($half - 1);
    }

    private function parseDecimal(&$rule, $field)
    {
        $rule[] = 'number';
        $rule[] = 'regex:\d{1,' . ($field['num_len'] - $field['num_scale']) . '}' . ($field['num_scale'] > 0 ? '(\.\d{1,' . $field['num_scale'] . '})?' : '');
    }

    private function parseString(&$rule, $field)
    {
        $rule[] = 'max:' . $field['char_max_len'];
    }

    private function parseEnum(&$rule, $field)
    {
        $rule[] = 'in:' . str_replace(['enum(', ')', "'"], '', $field['col_type']);
    }

    private function parseDate(&$rule)
    {
        $rule[] = 'date';
    }

    /**
     * @author: Rudy
     * @time: 2018年2月7日
     * description:获取现有的已存在的验证器
     * @return array
     */
    private function getAllValidator()
    {
        $result = [];

        if (is_dir($this->output_path)) {
            $result = scandir($this->output_path);
        }

        if (!empty($result)) {
            unset($result[array_search('.', $result)]);
            unset($result[array_search('..', $result)]);
            foreach ($result as &$v) {
                $this->up2Line($v, '.php', $this->prefix);
            }
        }

        return $result;
    }

    private function up2Line(&$origin, $cut = '', $prefix = '', $split_char = '_')
    {
        //去掉不要的部分
        if ($cut != '') {
            $origin = str_replace($cut, '', $origin);
        }
        //大写变小写带下划线
        $origin = strtolower(substr(preg_replace("/([A-Z])/", "{$split_char}\\1", $origin), 1));
        //加前缀
        if ($prefix != '') {
            $origin = $prefix . $origin;
        }
    }
}