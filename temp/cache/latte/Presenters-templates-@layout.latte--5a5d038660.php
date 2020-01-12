<?php
// source: E:\wamp64\www\nette-maturita\app\Presenters/templates/@layout.latte

use Latte\Runtime as LR;

class Template5a5d038660 extends Latte\Runtime\Template
{
    public $blocks = [
        'scripts' => 'blockScripts',
    ];

    public $blockTypes = [
        'scripts' => 'html',
    ];


    function main()
    {
        extract($this->params);
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width">
            <meta http-equiv="X-UA-Compatible" content="ie=edge">
            <link rel="stylesheet"
                  href="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 7 */ ?>/res/css/bootstrap.min.css">
            <script src="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 8 */ ?>/res/js/jquery-3.4.1.min.js"></script>
            <script src="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 9 */ ?>/res/js/popper.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
            <link rel="stylesheet"
                  href="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 11 */ ?>/res/css/index.css">
            <link rel="stylesheet"
                  href="<?php echo LR\Filters::escapeHtmlAttr(LR\Filters::safeUrl($basePath)) /* line 12 */ ?>/res/css/all.min.css">

            <title><?php
                if (isset($this->blockQueue["title"])) {
                    $this->renderBlock('title', $this->params, function ($s, $type) {
                        $_fi = new LR\FilterInfo($type);
                        return LR\Filters::convertTo($_fi, 'html', $this->filters->filterContent('striphtml', $_fi, $s));
                    });
                    ?> | <?php
                }
                ?>Kratos</title>
        </head>

        <body>
        <?php
        /* line 18 */
        $this->createTemplate("nav.latte", $this->params, "include")->renderToContentType('html');
        if ($flashes != null) {
            ?>
            <div class="col-md-12">
            <?php
            $iterations = 0;
            foreach ($flashes as $flash) {
                ?>
                <div<?php if ($_tmp = array_filter(['flash', $flash->type])) echo ' class="', LR\Filters::escapeHtmlAttr(implode(" ", array_unique($_tmp))), '"' ?>>
                    <div class="alert alert-danger m-4" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo LR\Filters::escapeHtmlText($flash->message) /* line 22 */ ?></span>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <?php
                $iterations++;
            }
            ?>
            </div>
            <?php
        }
        $this->renderBlock('content', $this->params, 'html');
        ?>

        <?php
        if ($this->getParentName()) return get_defined_vars();
        $this->renderBlock('scripts', get_defined_vars());
        ?>
        </body>
        </html>
        <?php
        return get_defined_vars();
    }


    function prepare()
    {
        extract($this->params);
        if (!$this->getReferringTemplate() || $this->getReferenceType() === "extends") {
            if (isset($this->params['flash'])) trigger_error('Variable $flash overwritten in foreach on line 20');
        }
        Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);

    }


    function blockScripts($_args)
    {

    }

}
