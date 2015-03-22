<?php

set_include_path(dirname(__FILE__).PATH_SEPARATOR.get_include_path());
require_once dirname(__FILE__).'/Test/View2.php';
require_once dirname(__FILE__).'/Test/View3.php';
require_once dirname(__FILE__).'/Test/TemplateView.php';

class HtmlTest extends PHPUnit_Framework_TestCase
{
    public function testViewWithHtml()
    {
        $view = new Test\View2;
        $out = "$view";
        $this->assertEquals("<h1>Hello world!</h1>\n", $out);
    }

    public function testViewTemplate()
    {
        $view = new Test\View2;
        $template = new Test\TemplateView(['helloWorld' => $view]);
        $out = "$template";
        $this->assertEquals(<<<EOT
<div>
    <h1>Hello world!</h1>
</div>

EOT
            ,
            $out
        );
    }

    public function testViewWithData()
    {
        $view = new Test\View3;
        $out = "$view";
        $this->assertEquals("<h1>Hello Mars!</h1>\n", $out);
    }

    public function testViewDataPersists()
    {
        $view = new Test\View2(['test' => 'this is injected']);
        $template = new Test\TemplateView(['helloWorld' => "$view"]);
        $out = "$template";
        $this->assertEquals(<<<EOT
<div>this is injected
    <h1>Hello world!</h1>
</div>

EOT
            ,
            $out
        );
    }
}

