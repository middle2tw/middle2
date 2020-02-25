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

/* database/structure/table_header.twig */
class __TwigTemplate_d66621867158ce07bea8471a8a6be75f521225ae8bd93ff5d61510a0c9c8a60d extends \Twig\Template
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
        echo "<form method=\"post\" action=\"db_structure.php\" name=\"tablesForm\" id=\"tablesForm\">
";
        // line 2
        echo PhpMyAdmin\Url::getHiddenInputs(($context["db"] ?? null));
        echo "
<div class=\"responsivetable\">
<table id=\"structureTable\" class=\"data\">
    <thead>
        <tr>
            <th class=\"print_ignore\"></th>
            <th>";
        // line 8
        echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Table"), "table");
        echo "</th>
            ";
        // line 9
        if (($context["replication"] ?? null)) {
            // line 10
            echo "                <th>";
            echo _gettext("Replication");
            echo "</th>
            ";
        }
        // line 12
        echo "
            ";
        // line 13
        if (($context["db_is_system_schema"] ?? null)) {
            // line 14
            echo "                ";
            $context["action_colspan"] = 3;
            // line 15
            echo "            ";
        } else {
            // line 16
            echo "                ";
            $context["action_colspan"] = 6;
            // line 17
            echo "            ";
        }
        // line 18
        echo "            ";
        if ((($context["num_favorite_tables"] ?? null) > 0)) {
            // line 19
            echo "                ";
            $context["action_colspan"] = (($context["action_colspan"] ?? null) + 1);
            // line 20
            echo "            ";
        }
        // line 21
        echo "            <th colspan=\"";
        echo twig_escape_filter($this->env, ($context["action_colspan"] ?? null), "html", null, true);
        echo "\" class=\"print_ignore\">
                ";
        // line 22
        echo _gettext("Action");
        // line 23
        echo "            </th>
            ";
        // line 25
        echo "            <th>
                ";
        // line 26
        echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Rows"), "records", "DESC");
        echo "
                ";
        // line 27
        echo PhpMyAdmin\Util::showHint(PhpMyAdmin\Sanitize::sanitize(_gettext("May be approximate. Click on the number to get the exact count. See [doc@faq3-11]FAQ 3.11[/doc].")));
        // line 29
        echo "
            </th>
            ";
        // line 31
        if ( !(($context["properties_num_columns"] ?? null) > 1)) {
            // line 32
            echo "                <th>";
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Type"), "type");
            echo "</th>
                <th>";
            // line 33
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Collation"), "collation");
            echo "</th>
            ";
        }
        // line 35
        echo "
            ";
        // line 36
        if (($context["is_show_stats"] ?? null)) {
            // line 37
            echo "                ";
            // line 38
            echo "                <th>";
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Size"), "size", "DESC");
            echo "</th>
                ";
            // line 40
            echo "                <th>";
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Overhead"), "overhead", "DESC");
            echo "</th>
            ";
        }
        // line 42
        echo "
            ";
        // line 43
        if (($context["show_charset"] ?? null)) {
            // line 44
            echo "                <th>";
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Charset"), "charset");
            echo "</th>
            ";
        }
        // line 46
        echo "
            ";
        // line 47
        if (($context["show_comment"] ?? null)) {
            // line 48
            echo "                <th>";
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Comment"), "comment");
            echo "</th>
            ";
        }
        // line 50
        echo "
            ";
        // line 51
        if (($context["show_creation"] ?? null)) {
            // line 52
            echo "                ";
            // line 53
            echo "                <th>";
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Creation"), "creation", "DESC");
            echo "</th>
            ";
        }
        // line 55
        echo "
            ";
        // line 56
        if (($context["show_last_update"] ?? null)) {
            // line 57
            echo "                ";
            // line 58
            echo "                <th>";
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Last update"), "last_update", "DESC");
            echo "</th>
            ";
        }
        // line 60
        echo "
            ";
        // line 61
        if (($context["show_last_check"] ?? null)) {
            // line 62
            echo "                ";
            // line 63
            echo "                <th>";
            echo PhpMyAdmin\Util::sortableTableHeader(_gettext("Last check"), "last_check", "DESC");
            echo "</th>
            ";
        }
        // line 65
        echo "        </tr>
    </thead>
    <tbody>
    ";
        // line 68
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["structure_table_rows"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["structure_table_row"]) {
            // line 69
            echo "        ";
            $this->loadTemplate("database/structure/structure_table_row.twig", "database/structure/table_header.twig", 69)->display(twig_to_array($context["structure_table_row"]));
            // line 70
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['structure_table_row'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 71
        echo "    </tbody>
    ";
        // line 72
        if (($context["body_for_table_summary"] ?? null)) {
            // line 73
            echo "        ";
            $this->loadTemplate("database/structure/body_for_table_summary.twig", "database/structure/table_header.twig", 73)->display(twig_to_array(($context["body_for_table_summary"] ?? null)));
            // line 74
            echo "    ";
        }
        // line 75
        echo "</table>
</div>
";
        // line 77
        if (($context["check_all_tables"] ?? null)) {
            // line 78
            echo "    ";
            $this->loadTemplate("database/structure/check_all_tables.twig", "database/structure/table_header.twig", 78)->display(twig_to_array(($context["check_all_tables"] ?? null)));
        }
        // line 80
        echo "</form>
";
    }

    public function getTemplateName()
    {
        return "database/structure/table_header.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  233 => 80,  229 => 78,  227 => 77,  223 => 75,  220 => 74,  217 => 73,  215 => 72,  212 => 71,  206 => 70,  203 => 69,  199 => 68,  194 => 65,  188 => 63,  186 => 62,  184 => 61,  181 => 60,  175 => 58,  173 => 57,  171 => 56,  168 => 55,  162 => 53,  160 => 52,  158 => 51,  155 => 50,  149 => 48,  147 => 47,  144 => 46,  138 => 44,  136 => 43,  133 => 42,  127 => 40,  122 => 38,  120 => 37,  118 => 36,  115 => 35,  110 => 33,  105 => 32,  103 => 31,  99 => 29,  97 => 27,  93 => 26,  90 => 25,  87 => 23,  85 => 22,  80 => 21,  77 => 20,  74 => 19,  71 => 18,  68 => 17,  65 => 16,  62 => 15,  59 => 14,  57 => 13,  54 => 12,  48 => 10,  46 => 9,  42 => 8,  33 => 2,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "database/structure/table_header.twig", "/home/srwang/work/middle2/webroot/phpMyAdmin/templates/database/structure/table_header.twig");
    }
}
