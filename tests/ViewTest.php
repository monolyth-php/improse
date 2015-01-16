<?php

use Improse\View;
use Improse\Render\Html;

class TestView1 extends View
{
    public function __invoke()
    {
        return 'Hello world!';
    }
}

class TestView2 extends View
{
    public function __invoke()
    {
        $out = new Html(realpath(__DIR__).'/helloworld.php');
        return $out();
    }
}

class ViewTest extends PHPUnit_Framework_TestCase
{
    public function testView()
    {
        $view = new TestView1;
        $out = $view();
        $this->assertEquals('Hello world!', $out);
    }

    public function testViewWithHtml()
    {
        $view = new TestView2;
        ob_start();
        $view();
        $out = ob_get_clean();
        $this->assertEquals("<h1>Hello world!</h1>\n", $out);
    }

    public function testViewTemplate()
    {
        $view = new TestView2;
        $template = new Html(realpath(__DIR__).'/template.php');
        ob_start();
        $template(['helloWorld' => $view]);
        $out = ob_get_clean();
        $this->assertEquals(<<<EOT
<div>
    <h1>Hello world!</h1>
</div>

EOT
            ,
            $out
        );
    }
}

