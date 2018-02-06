
/**
*
* auto-generated:{$time}
*
*/
namespace app\common\validate;

use think\Validate;

class {$validator} extends Validate
{
    protected $rule = [
        {volist name="rules" id="rule"}
'{$key}' => '{$rule}',
        {/volist}

    ];

    protected $field = [
        {volist name="fields" id="field"}
'{$key}' => '{$field}',
        {/volist}

    ];

    protected $message = [
    ];

    protected $scene = [
        'add' => [],
        'edit' => [],
    ];

}