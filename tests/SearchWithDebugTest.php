<?php


class SearchWithDebugTest extends SearchTest
{
    public function setUp(): void
    {
        parent::setUp();
        $this->tidal->debug = true;
        ob_start();
    }
    public function tearDown(): void
    {
        parent::tearDown();
        ob_end_clean();
    }
}