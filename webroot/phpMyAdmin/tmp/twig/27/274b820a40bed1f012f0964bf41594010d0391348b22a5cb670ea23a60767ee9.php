<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* database/structure/favorite_anchor.twig */
class __TwigTemplate_ad564863a47fae33286cd2825d914595cb79ef79652f0f0b1b1a43e4ebb02934 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<a id=\"";
        echo twig_escape_filter($this->env, ($context["table_name_hash"] ?? null), "html", null, true);
        echo "_favorite_anchor\"
    class=\"ajax favorite_table_anchor\"
    href=\"db_structure.php";
        // line 3
        echo PhpMyAdmin\Url::getCommon(($context["fav_params"] ?? null));
        echo "\"
    title=\"";
        // line 4
        echo twig_escape_filter($this->env, ((($context["already_favorite"] ?? null)) ? (_gettext("Remove from Favorites")) : (_gettext("Add to Favorites"))), "html", null, true);
        echo "\"
    data-favtargets=\"";
        // line 5
        echo twig_escape_filter($this->env, ($context["db_table_name_hash"] ?? null), "html", null, true);
        echo "\" >
    ";
        // line 6
        echo ((($context["already_favorite"] ?? null)) ? ($this->getAttribute(($context["titles"] ?? null), "Favorite", [], "array")) : ($this->getAttribute(($context["titles"] ?? null), "NoFavorite", [], "array")));
        echo "
</a>
";
    }

    public function getTemplateName()
    {
        return "database/structure/favorite_anchor.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  48 => 6,  44 => 5,  40 => 4,  36 => 3,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "database/structure/favorite_anchor.twig", "/home/srwang/work/middle2/webroot/phpMyAdmin/templates/database/structure/favorite_anchor.twig");
    }
}
