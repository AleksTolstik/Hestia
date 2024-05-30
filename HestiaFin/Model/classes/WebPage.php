<?php

class WebPage
{
    protected $body;

    public function __construct($body)
    {
        $this->body = $body;
    }

    public function displayBody()
    {
        echo "<main>{$this->body}</main>";
    }

    public function displayPage()
    {
        echo "<body>";
        $this->displayBody();
        echo "</body>";
    }
}
