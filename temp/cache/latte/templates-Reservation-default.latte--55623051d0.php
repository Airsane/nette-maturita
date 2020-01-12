<?php
// source: E:\wamp64\www\nette-maturita\app\Presenters/templates/Reservation/default.latte

use Latte\Runtime as LR;

class Template55623051d0 extends Latte\Runtime\Template
{
    public $blocks = [
        'content' => 'blockContent',
    ];

    public $blockTypes = [
        'content' => 'html',
    ];


    function main()
    {
        extract($this->params);
        if ($this->getParentName()) return get_defined_vars();
        $this->renderBlock('content', get_defined_vars());
        return get_defined_vars();
    }


    function prepare()
    {
        extract($this->params);
        Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);

    }


    function blockContent($_args)
    {
        ?>
        <div class="search-bar mx-sm-auto">
        <form class=" form-inline p-md-4 p-sm-0 ">
            <input class="form-control mr-sm-2" type="text" placeholder="Např. Pardubice">
            <input class="form-control mr-sm-2" type="date"
                   value="<?php echo LR\Filters::escapeHtmlAttr(date('Y-m-d')) /* line 7 */ ?>" name="">
            <input class="form-control mr-sm-2" type="date"
                   value="<?php echo LR\Filters::escapeHtmlAttr(date('Y-m-d', strtotime("tomorrow"))) /* line 8 */ ?>"
                   name="">
            <input type="button" class="form-control mr-sm-2" value="Hledat">
        </form>
        </div>


        <div class="container-fluid p-4">
            <div class="row">
                <h1>kys</h1>
            </div>
        </div>
        <?php
    }

}
