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

/* database/structure/structure_table_row.twig */
class __TwigTemplate_9aa73fa6aebc50f472c3bb7db36298dc465053678880cbdc9a6d107ffa05cd5d extends \Twig\Template
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
        echo "<tr id=\"row_tbl_";
        echo twig_escape_filter($this->env, ($context["curr"] ?? null), "html", null, true);
        echo "\"";
        echo ((($context["table_is_view"] ?? null)) ? (" class=\"is_view\"") : (""));
        echo " data-filter-row=\"";
        echo twig_escape_filter($this->env, twig_upper_filter($this->env, $this->getAttribute(($context["current_table"] ?? null), "TABLE_NAME", [], "array")), "html", null, true);
        echo "\">
    <td class=\"center print_ignore\">
        <input type=\"checkbox\"
            name=\"selected_tbl[]\"
            class=\"";
        // line 5
        echo twig_escape_filter($this->env, ($context["input_class"] ?? null), "html", null, true);
        echo "\"
            value=\"";
        // line 6
        echo twig_escape_filter($this->env, $this->getAttribute(($context["current_table"] ?? null), "TABLE_NAME", [], "array"), "html", null, true);
        echo "\"
            id=\"checkbox_tbl_";
        // line 7
        echo twig_escape_filter($this->env, ($context["curr"] ?? null), "html", null, true);
        echo "\" />
    </td>
    <th>
        ";
        // line 10
        echo ($context["browse_table_label"] ?? null);
        echo "
        ";
        // line 11
        echo ($context["tracking_icon"] ?? null);
        echo "
    </th>
    ";
        // line 13
        if (($context["server_slave_status"] ?? null)) {
            // line 14
            echo "        <td class=\"center\">
            ";
            // line 15
            echo ((($context["ignored"] ?? null)) ? (PhpMyAdmin\Util::getImage("s_cancel", _gettext("Not replicated"))) : (""));
            echo "
            ";
            // line 16
            echo ((($context["do"] ?? null)) ? (PhpMyAdmin\Util::getImage("s_success", _gettext("Replicated"))) : (""));
            echo "
        </td>
    ";
        }
        // line 19
        echo "
    ";
        // line 21
        echo "    ";
        if ((($context["num_favorite_tables"] ?? null) > 0)) {
            // line 22
            echo "        <td class=\"center print_ignore\">
            ";
            // line 24
            echo "            ";
            $context["fav_params"] = ["db" =>             // line 25
($context["db"] ?? null), "ajax_request" => true, "favorite_table" => $this->getAttribute(            // line 27
($context["current_table"] ?? null), "TABLE_NAME", [], "array"), (((            // line 28
($context["already_favorite"] ?? null)) ? ("remove") : ("add")) . "_favorite") => true];
            // line 30
            echo "            ";
            $this->loadTemplate("database/structure/favorite_anchor.twig", "database/structure/structure_table_row.twig", 30)->display(twig_to_array(["table_name_hash" => md5($this->getAttribute(            // line 31
($context["current_table"] ?? null), "TABLE_NAME", [], "array")), "db_table_name_hash" => md5(((            // line 32
($context["db"] ?? null) . ".") . $this->getAttribute(($context["current_table"] ?? null), "TABLE_NAME", [], "array"))), "fav_params" =>             // line 33
($context["fav_params"] ?? null), "already_favorite" =>             // line 34
($context["already_favorite"] ?? null), "titles" =>             // line 35
($context["titles"] ?? null)]));
            // line 37
            echo "        </td>
    ";
        }
        // line 39
        echo "
    <td class=\"center print_ignore\">
        ";
        // line 41
        echo ($context["browse_table"] ?? null);
        echo "
    </td>
    <td class=\"center print_ignore\">
        <a href=\"tbl_structure.php";
        // line 44
        echo ($context["tbl_url_query"] ?? null);
        echo "\">
            ";
        // line 45
        echo $this->getAttribute(($context["titles"] ?? null), "Structure", [], "array");
        echo "
        </a>
    </td>
    <td class=\"center print_ignore\">
        ";
        // line 49
        echo ($context["search_table"] ?? null);
        echo "
    </td>

    ";
        // line 52
        if ( !($context["db_is_system_schema"] ?? null)) {
            // line 53
            echo "        <td class=\"insert_table center print_ignore\">
            <a href=\"tbl_change.php";
            // line 54
            echo ($context["tbl_url_query"] ?? null);
            echo "\">";
            echo $this->getAttribute(($context["titles"] ?? null), "Insert", [], "array");
            echo "</a>
        </td>
        <td class=\"center print_ignore\">";
            // line 56
            echo ($context["empty_table"] ?? null);
            echo "</td>
        <td class=\"center print_ignore\">
            <a class=\"ajax drop_table_anchor";
            // line 59
            echo (((($context["table_is_view"] ?? null) || ($this->getAttribute(($context["current_table"] ?? null), "ENGINE", [], "array") == null))) ? (" view") : (""));
            echo "\"
                href=\"sql.php\" data-post=\"";
            // line 60
            echo ($context["tbl_url_query"] ?? null);
            echo "&amp;reload=1&amp;purge=1&amp;sql_query=";
            // line 61
            echo twig_escape_filter($this->env, twig_urlencode_filter(($context["drop_query"] ?? null)), "html", null, true);
            echo "&amp;message_to_show=";
            echo twig_escape_filter($this->env, twig_urlencode_filter(($context["drop_message"] ?? null)), "html", null, true);
            echo "\">
                ";
            // line 62
            echo $this->getAttribute(($context["titles"] ?? null), "Drop", [], "array");
            echo "
            </a>
        </td>
    ";
        }
        // line 66
        echo "
    ";
        // line 67
        if (($this->getAttribute(($context["current_table"] ?? null), "TABLE_ROWS", [], "array", true, true) && (($this->getAttribute(        // line 68
($context["current_table"] ?? null), "ENGINE", [], "array") != null) || ($context["table_is_view"] ?? null)))) {
            // line 69
            echo "        ";
            // line 70
            echo "        ";
            $context["row_count"] = PhpMyAdmin\Util::formatNumber($this->getAttribute(($context["current_table"] ?? null), "TABLE_ROWS", [], "array"), 0);
            // line 71
            echo "
        ";
            // line 74
            echo "        <td class=\"value tbl_rows\"
            data-table=\"";
            // line 75
            echo twig_escape_filter($this->env, $this->getAttribute(($context["current_table"] ?? null), "TABLE_NAME", [], "array"), "html", null, true);
            echo "\">
            ";
            // line 76
            if (($context["approx_rows"] ?? null)) {
                // line 77
                echo "                <a href=\"db_structure.php";
                echo PhpMyAdmin\Url::getCommon(["ajax_request" => true, "db" =>                 // line 79
($context["db"] ?? null), "table" => $this->getAttribute(                // line 80
($context["current_table"] ?? null), "TABLE_NAME", [], "array"), "real_row_count" => "true"]);
                // line 82
                echo "\" class=\"ajax real_row_count\">
                    <bdi>
                        ~";
                // line 84
                echo twig_escape_filter($this->env, ($context["row_count"] ?? null), "html", null, true);
                echo "
                    </bdi>
                </a>
            ";
            } else {
                // line 88
                echo "                ";
                echo twig_escape_filter($this->env, ($context["row_count"] ?? null), "html", null, true);
                echo "
            ";
            }
            // line 90
            echo "            ";
            echo ($context["show_superscript"] ?? null);
            echo "
        </td>

        ";
            // line 93
            if ( !(($context["properties_num_columns"] ?? null) > 1)) {
                // line 94
                echo "            <td class=\"nowrap\">
                ";
                // line 95
                if ( !twig_test_empty($this->getAttribute(($context["current_table"] ?? null), "ENGINE", [], "array"))) {
                    // line 96
                    echo "                    ";
                    echo twig_escape_filter($this->env, $this->getAttribute(($context["current_table"] ?? null), "ENGINE", [], "array"), "html", null, true);
                    echo "
                ";
                } elseif (                // line 97
($context["table_is_view"] ?? null)) {
                    // line 98
                    echo "                    ";
                    echo _gettext("View");
                    // line 99
                    echo "                ";
                }
                // line 100
                echo "            </td>
            ";
                // line 101
                if ((twig_length_filter($this->env, ($context["collation"] ?? null)) > 0)) {
                    // line 102
                    echo "                <td class=\"nowrap\">
                    ";
                    // line 103
                    echo ($context["collation"] ?? null);
                    echo "
                </td>
            ";
                }
                // line 106
                echo "        ";
            }
            // line 107
            echo "
        ";
            // line 108
            if (($context["is_show_stats"] ?? null)) {
                // line 109
                echo "            <td class=\"value tbl_size\">
                <a href=\"tbl_structure.php";
                // line 110
                echo ($context["tbl_url_query"] ?? null);
                echo "#showusage\">
                    <span>";
                // line 111
                echo twig_escape_filter($this->env, ($context["formatted_size"] ?? null), "html", null, true);
                echo "</span>
                    <span class=\"unit\">";
                // line 112
                echo twig_escape_filter($this->env, ($context["unit"] ?? null), "html", null, true);
                echo "</span>
                </a>
            </td>
            <td class=\"value tbl_overhead\">
                ";
                // line 116
                echo ($context["overhead"] ?? null);
                echo "
            </td>
        ";
            }
            // line 119
            echo "
        ";
            // line 120
            if ( !(($context["show_charset"] ?? null) > 1)) {
                // line 121
                echo "            ";
                if ((twig_length_filter($this->env, ($context["charset"] ?? null)) > 0)) {
                    // line 122
                    echo "                <td class=\"nowrap\">
                    ";
                    // line 123
                    echo ($context["charset"] ?? null);
                    echo "
                </td>
            ";
                }
                // line 126
                echo "        ";
            }
            // line 127
            echo "
        ";
            // line 128
            if (($context["show_comment"] ?? null)) {
                // line 129
                echo "            ";
                $context["comment"] = $this->getAttribute(($context["current_table"] ?? null), "Comment", [], "array");
                // line 130
                echo "            <td>
                ";
                // line 131
                if ((twig_length_filter($this->env, ($context["comment"] ?? null)) > ($context["limit_chars"] ?? null))) {
                    // line 132
                    echo "                    <abbr title=\"";
                    echo twig_escape_filter($this->env, ($context["comment"] ?? null), "html", null, true);
                    echo "\">
                        ";
                    // line 133
                    echo twig_escape_filter($this->env, twig_slice($this->env, ($context["comment"] ?? null), 0, ($context["limit_chars"] ?? null)), "html", null, true);
                    echo "
                        ...
                    </abbr>
                ";
                } else {
                    // line 137
                    echo "                    ";
                    echo twig_escape_filter($this->env, ($context["comment"] ?? null), "html", null, true);
                    echo "
                ";
                }
                // line 139
                echo "            </td>
        ";
            }
            // line 141
            echo "
        ";
            // line 142
            if (($context["show_creation"] ?? null)) {
                // line 143
                echo "            <td class=\"value tbl_creation\">
                ";
                // line 144
                ((($context["create_time"] ?? null)) ? (print (twig_escape_filter($this->env, PhpMyAdmin\Util::localisedDate(strtotime(($context["create_time"] ?? null))), "html", null, true))) : (print ("-")));
                echo "
            </td>
        ";
            }
            // line 147
            echo "
        ";
            // line 148
            if (($context["show_last_update"] ?? null)) {
                // line 149
                echo "            <td class=\"value tbl_last_update\">
                ";
                // line 150
                ((($context["update_time"] ?? null)) ? (print (twig_escape_filter($this->env, PhpMyAdmin\Util::localisedDate(strtotime(($context["update_time"] ?? null))), "html", null, true))) : (print ("-")));
                echo "
            </td>
        ";
            }
            // line 153
            echo "
        ";
            // line 154
            if (($context["show_last_check"] ?? null)) {
                // line 155
                echo "            <td class=\"value tbl_last_check\">
                ";
                // line 156
                ((($context["check_time"] ?? null)) ? (print (twig_escape_filter($this->env, PhpMyAdmin\Util::localisedDate(strtotime(($context["check_time"] ?? null))), "html", null, true))) : (print ("-")));
                echo "
            </td>
        ";
            }
            // line 159
            echo "
    ";
        } elseif (        // line 160
($context["table_is_view"] ?? null)) {
            // line 161
            echo "        <td class=\"value tbl_rows\">-</td>
        <td class=\"nowrap\">
            ";
            // line 163
            echo _gettext("View");
            // line 164
            echo "        </td>
        <td class=\"nowrap\">---</td>
        ";
            // line 166
            if (($context["is_show_stats"] ?? null)) {
                // line 167
                echo "            <td class=\"value tbl_size\">-</td>
            <td class=\"value tbl_overhead\">-</td>
        ";
            }
            // line 170
            echo "        ";
            if (($context["show_charset"] ?? null)) {
                // line 171
                echo "            <td></td>
        ";
            }
            // line 173
            echo "        ";
            if (($context["show_comment"] ?? null)) {
                // line 174
                echo "            <td></td>
        ";
            }
            // line 176
            echo "        ";
            if (($context["show_creation"] ?? null)) {
                // line 177
                echo "            <td class=\"value tbl_creation\">-</td>
        ";
            }
            // line 179
            echo "        ";
            if (($context["show_last_update"] ?? null)) {
                // line 180
                echo "            <td class=\"value tbl_last_update\">-</td>
        ";
            }
            // line 182
            echo "        ";
            if (($context["show_last_check"] ?? null)) {
                // line 183
                echo "            <td class=\"value tbl_last_check\">-</td>
        ";
            }
            // line 185
            echo "
    ";
        } else {
            // line 187
            echo "        ";
            $context["count"] = 0;
            // line 188
            echo "        ";
            if (($context["properties_num_columns"] ?? null)) {
                // line 189
                echo "            ";
                $context["count"] = (($context["count"] ?? null) + 2);
                // line 190
                echo "        ";
            }
            // line 191
            echo "        ";
            if (($context["is_show_stats"] ?? null)) {
                // line 192
                echo "            ";
                $context["count"] = (($context["count"] ?? null) + 2);
                // line 193
                echo "        ";
            }
            // line 194
            echo "        ";
            if (($context["show_charset"] ?? null)) {
                // line 195
                echo "            ";
                $context["count"] = (($context["count"] ?? null) + 1);
                // line 196
                echo "        ";
            }
            // line 197
            echo "        ";
            if (($context["show_comment"] ?? null)) {
                // line 198
                echo "            ";
                $context["count"] = (($context["count"] ?? null) + 1);
                // line 199
                echo "        ";
            }
            // line 200
            echo "        ";
            if (($context["show_creation"] ?? null)) {
                // line 201
                echo "            ";
                $context["count"] = (($context["count"] ?? null) + 1);
                // line 202
                echo "        ";
            }
            // line 203
            echo "        ";
            if (($context["show_last_update"] ?? null)) {
                // line 204
                echo "            ";
                $context["count"] = (($context["count"] ?? null) + 1);
                // line 205
                echo "        ";
            }
            // line 206
            echo "        ";
            if (($context["show_last_check"] ?? null)) {
                // line 207
                echo "            ";
                $context["count"] = (($context["count"] ?? null) + 1);
                // line 208
                echo "        ";
            }
            // line 209
            echo "
        ";
            // line 210
            if (($context["db_is_system_schema"] ?? null)) {
                // line 211
                echo "            ";
                $context["action_colspan"] = 3;
                // line 212
                echo "        ";
            } else {
                // line 213
                echo "            ";
                $context["action_colspan"] = 6;
                // line 214
                echo "        ";
            }
            // line 215
            echo "        ";
            if ((($context["num_favorite_tables"] ?? null) > 0)) {
                // line 216
                echo "            ";
                $context["action_colspan"] = (($context["action_colspan"] ?? null) + 1);
                // line 217
                echo "        ";
            }
            // line 218
            echo "
        ";
            // line 219
            $context["colspan_for_structure"] = (($context["action_colspan"] ?? null) + 3);
            // line 220
            echo "        <td colspan=\"";
            echo (((($context["colspan_for_structure"] ?? null) - ($context["db_is_system_schema"] ?? null))) ? (6) : (9));
            echo "\"
            class=\"center\">
            ";
            // line 222
            echo _gettext("in use");
            // line 223
            echo "        </td>
    ";
        }
        // line 225
        echo "</tr>
";
    }

    public function getTemplateName()
    {
        return "database/structure/structure_table_row.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  556 => 225,  552 => 223,  550 => 222,  544 => 220,  542 => 219,  539 => 218,  536 => 217,  533 => 216,  530 => 215,  527 => 214,  524 => 213,  521 => 212,  518 => 211,  516 => 210,  513 => 209,  510 => 208,  507 => 207,  504 => 206,  501 => 205,  498 => 204,  495 => 203,  492 => 202,  489 => 201,  486 => 200,  483 => 199,  480 => 198,  477 => 197,  474 => 196,  471 => 195,  468 => 194,  465 => 193,  462 => 192,  459 => 191,  456 => 190,  453 => 189,  450 => 188,  447 => 187,  443 => 185,  439 => 183,  436 => 182,  432 => 180,  429 => 179,  425 => 177,  422 => 176,  418 => 174,  415 => 173,  411 => 171,  408 => 170,  403 => 167,  401 => 166,  397 => 164,  395 => 163,  391 => 161,  389 => 160,  386 => 159,  380 => 156,  377 => 155,  375 => 154,  372 => 153,  366 => 150,  363 => 149,  361 => 148,  358 => 147,  352 => 144,  349 => 143,  347 => 142,  344 => 141,  340 => 139,  334 => 137,  327 => 133,  322 => 132,  320 => 131,  317 => 130,  314 => 129,  312 => 128,  309 => 127,  306 => 126,  300 => 123,  297 => 122,  294 => 121,  292 => 120,  289 => 119,  283 => 116,  276 => 112,  272 => 111,  268 => 110,  265 => 109,  263 => 108,  260 => 107,  257 => 106,  251 => 103,  248 => 102,  246 => 101,  243 => 100,  240 => 99,  237 => 98,  235 => 97,  230 => 96,  228 => 95,  225 => 94,  223 => 93,  216 => 90,  210 => 88,  203 => 84,  199 => 82,  197 => 80,  196 => 79,  194 => 77,  192 => 76,  188 => 75,  185 => 74,  182 => 71,  179 => 70,  177 => 69,  175 => 68,  174 => 67,  171 => 66,  164 => 62,  158 => 61,  155 => 60,  151 => 59,  146 => 56,  139 => 54,  136 => 53,  134 => 52,  128 => 49,  121 => 45,  117 => 44,  111 => 41,  107 => 39,  103 => 37,  101 => 35,  100 => 34,  99 => 33,  98 => 32,  97 => 31,  95 => 30,  93 => 28,  92 => 27,  91 => 25,  89 => 24,  86 => 22,  83 => 21,  80 => 19,  74 => 16,  70 => 15,  67 => 14,  65 => 13,  60 => 11,  56 => 10,  50 => 7,  46 => 6,  42 => 5,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "database/structure/structure_table_row.twig", "/home/srwang/work/middle2/webroot/phpMyAdmin/templates/database/structure/structure_table_row.twig");
    }
}
