<?php

require_once(__DIR__."/FactoryTest.php");

/**
 * This tests the actual implementation of the factory.
 */
class FactoryImplementationTest extends FactoryTest {
    public function getFactoryInstance() {
        throw new Exception("Fabian, please implement me!");
    }
}