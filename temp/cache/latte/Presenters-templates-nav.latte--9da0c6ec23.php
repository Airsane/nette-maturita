<?php
// source: E:\wamp64\www\nette-maturita\app\Presenters\templates\nav.latte

use Latte\Runtime as LR;

class Template9da0c6ec23 extends Latte\Runtime\Template
{

    function main()
    {
        extract($this->params);
        ?>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <a class="navbar-brand"
               href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link("Homepage:default")) ?>">Kratos</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01"
                    aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarColor01">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link"
                           href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link("Homepage:default")) ?>">Domů
                            <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Doporučené ubytování</a>
                    </li>
                </ul>
                <?php
                if (!$user->isLoggedIn()) {
                    ?>
                    <div class="my-2 my-lg-0"><a href="#" class="nav-link" data-toggle="modal"
                                                 data-target="#loginModal"><em
                                    class="fas fa-lock"></em> Přihlášení</a></div>
                    <?php
                } else {
                    ?>
                    <div class="dropdown my-2 my-lg-0">
                        <a href="#" class="nav-link" data-toggle="dropdown">
                            <em class="fas fa-user"></em> <?php echo LR\Filters::escapeHtmlText($user->getIdentity()->firstname) /* line 23 */ ?> <?php
                            echo LR\Filters::escapeHtmlText($user->getIdentity()->lastname) /* line 23 */ ?>

                        </a>
                        <div class="dropdown-menu dropdown-menu-right bg-primary">
                            <?php
                            if ($user->getRoles()[0] == 1) {
                                ?>                        <a class="dropdown-item"
                                                             href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link(":Admin:Dashboard:default")) ?>"><em
                                            class="fas fa-bookmark"></em> Administrace</a>
                                <?php
                            }
                            ?>
                            <a class="dropdown-item" href="#"><em class="fas fa-bookmark"></em> Rezervace</a>
                            <a class="dropdown-item" href="#"><em class="fas fa-cogs"></em> Nastavení</a>
                            <a class="dropdown-item"
                               href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link("Sign:out")) ?>"><em
                                        class="fas fa-lock"></em> Odhlásit se</a>
                        </div>
                    </div>

                    <?php
                }
                ?>
            </div>
        </nav>

        <div id="loginModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Přihlášení</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <a class="float-right"
                       href="<?php echo LR\Filters::escapeHtmlAttr($this->global->uiControl->link("Sign:up")) ?>">Nový
                        uživatel?</a>
                    <?php
                    /* line 48 */
                    $_tmp = $this->global->uiControl->getComponent("loginForm");
                    if ($_tmp instanceof Nette\Application\UI\IRenderable) $_tmp->redrawControl(null, false);
                    $_tmp->render();
                    ?>
                </div>
            </div>
        </div>
        </div><?php
        return get_defined_vars();
    }


    function prepare()
    {
        extract($this->params);
        Nette\Bridges\ApplicationLatte\UIRuntime::initialize($this, $this->parentName, $this->blocks);

    }

}
