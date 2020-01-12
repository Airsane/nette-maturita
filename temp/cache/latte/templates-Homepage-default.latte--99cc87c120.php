<?php
// source: E:\wamp64\www\nette-maturita\app\Presenters/templates/Homepage/default.latte

use Latte\Runtime as LR;

class Template99cc87c120 extends Latte\Runtime\Template
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
        if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
            if (isset($this->params['house'])) trigger_error('Variable $house overwritten in foreach on line 17');
        }
        Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);

    }


    function blockContent($_args)
    {
        extract($_args);
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
                <a href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link("Reservation:default")) ?>">Test</a>
                <?php
                $iterations = 0;
                foreach ($houses as $house) {
                    ?>
                    <div class="col-md-3 mb-4">
                        <div class="card">
                            <img
                                src="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 20 */ ?>/res/img/house/<?php
                                echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($house->image)) /* line 20 */ ?>"
                                class="card-img-top" alt="...">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo LR\Filters::escapeHtmlText($house->name) /* line 22 */ ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted"><em class="fas fa-map-pin"></em> <?php
                                    echo LR\Filters::escapeHtmlText($house->street) /* line 23 */ ?>

                                    - <?php echo LR\Filters::escapeHtmlText($house->city) /* line 24 */ ?></h6>
                                <div
                                    class="card-text"><?php echo LR\Filters::escapeHtmlText(($this->filters->truncate)(($this->filters->striptags)($house->description), 250)) /* line 25 */ ?> </div>
                                <a class="btn btn-primary"
                                   href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link("House:show", [$house->id])) ?>">Více
                                    informací</a>
                            </div>
                        </div>
                    </div>
                    <?php
                    $iterations++;
                }
                ?>
            </div>
        </div>
        <?php
    }

}
