<?php

/**
 * Benchmarking utility
 *
 * The code is not super-optimal, it's more like a real-life simulation
 *
 * @package Samples
 */

// compared to pure-php equivalent with no code/content separation: ~0.4 sec.
$start = microtime(true);
echo 'pure PHP: 10k loops [ ';
for ($i = 0; $i < 10; $i++) {
    if ($i) echo '| ';
    for ($k = 0; $k < 10; $k++) {
        ob_start();
        for ($j = 0; $j < 100; $j++) {
            run();
        }
        ob_end_clean();
        echo '. ';
        flush();
    }
}
echo '] '.(microtime(true)- $start).' sec.';

/**
 * Executes one pass
 */
function run() {

    if (!class_exists('_View')) {
        class _View {
            var $title = 'Page title';
            var $customVar = '<em>top level variable value</em>';

            function subTemplate() {
                return "1 - {$this->title}, 2 - {$this->customVar}";
            }

            function cycleTemplate($items) {
                $result = "";
                foreach ($items as $item) {
                    $result .= $this->itemTemplate($item);
                }
                return $result;
            }

            function itemTemplate($item) {
                return "+ $item <br/>";
            }
        }
    }
    $view = new _View();

    ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<!-- Sample template (html) file -->
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <!-- just a static variable -->
        <title><?php echo $view->title; ?></title>
    </head>
    <body>
            <?php /* This is a comment, hidden from the user */ ?>

        <!-- define a custom block html here -->
            <?php
            if (!class_exists('_ViewChild')) {
                class _ViewChild extends _View {
                    var $title = 'Subtitle (overrides upper level variable)';
                    var $info = 'Information <strong>with another variable: {$customVar}</strong>.';

                    function subTemplate() {
                        return "We change the template var @subTemplate (adding something new): <br />
                        1 - {$this->title}, 2 - {$this->customVar}, 3 - random numbers: ".$this->randomNo().", ".$this->randomNo();
                    }

                    function randomNo() {
                        return rand(1000, 9999);
                    }
                }
            }
            ?>

        <!-- same as above, but with support for cycles -->
            <?php
            if (!class_exists('_ViewChild2')) {
                class _ViewChild2 extends _View {

                    function cycleTemplate($items) {
                        $result = "<ul>";
                        foreach ($items as $item) {
                            $result .= $this->itemTemplate($item);
                        }
                        $result .= "</ul>";
                        return $result;
                    }

                    function itemTemplate($item) {
                        return "<li>$item</li>";
                    }
                }
            }
            ?>

        <!-- we use a template var here, 'caus it contains stuff that need to be parsed -->
            <?php
            $view = new _ViewChild();
            echo "Body content (block): <strong>templated</strong> - ".$view->subTemplate();
            ?>

        <!-- a wrapper var, triggers @cycleTemplate parsing -->
            <?php
            $persons = array(
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe',
                'Ana', 'Marina', 'Vasile', 'Victoria', 'Gheorghe', // 200 items
            );
            $view = new _ViewChild2();
            echo $view->cycleTemplate($persons);
            ?>
    </body>
</html>

<?php
}