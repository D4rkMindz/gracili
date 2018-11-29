<?php

namespace App\Test\Util;

use App\Test\BaseTestCase;
use App\Util\ValidationResult;

/**
 * AppControllerTestCase.
 *
 * @coversDefaultClass \App\Util\ValidationResult
 * @group actual
 */
class ValidationContextTest extends BaseTestCase
{
    /**
     * Test instance.
     *
     * @return void
     */
    public function testInstance()
    {
        $instance = new ValidationResult();
        $this->assertInstanceOf(ValidationResult::class, $instance);
        $this->assertSame('Please check your data', $instance->getMessage());
    }

    /**
     * Test set message method.
     *
     * @return void
     */
    public function testSetMessage()
    {
        $defaultMessage = 'Default message';
        $notDefaultMessage = 'Not default message';
        $validationContext = new ValidationResult($defaultMessage);
        $this->assertSame($defaultMessage, $validationContext->getMessage());
        $validationContext->setMessage($notDefaultMessage);
        $this->assertSame($notDefaultMessage, $validationContext->getMessage());
    }

    /**
     * Test set error method.
     *
     * @return void
     */
    public function testError()
    {
        $validationContext = new ValidationResult();
        $validationContext->setError('username', 'Username not valid');
        $validationContext->setError('password', 'Password not valid');
        $expected = [
            [
                'field' => 'username',
                'message' => 'Username not valid',
            ],
            [
                'field' => 'password',
                'message' => 'Password not valid',
            ],
        ];
        $this->assertSame($expected, $validationContext->getErrors());
    }

    /**
     * Test to array method.
     *
     * @return void
     */
    public function testToArray()
    {
        $validationContext = new ValidationResult();
        $validationContext->setError('username', 'Username not valid');
        $validationContext->setError('password', 'Password not valid');
        $expected = [
            'message' => 'Please check your data',
            'errors' => [
                [
                    'field' => 'username',
                    'message' => 'Username not valid',
                ],
                [
                    'field' => 'password',
                    'message' => 'Password not valid',
                ],
            ],
        ];
        $this->assertSame($expected, $validationContext->toArray());
    }

    /**
     * Test fails method.
     *
     * @return void
     */
    public function testFails()
    {
        $validationContext = new ValidationResult();
        $this->assertFalse($validationContext->fails());
        $validationContext->setError('username', 'Username not valid');
        $this->assertTrue($validationContext->fails());
    }

    /**
     * Test if success is correct.
     *
     * @return void
     */
    public function testSuccess()
    {
        $validationContext = new ValidationResult();
        $this->assertTrue($validationContext->success());
        $validationContext->setError('username', 'Username not valid');
        $this->assertFalse($validationContext->success());
    }

    /**
     * Test clear.
     *
     * @return void
     */
    public function testClear()
    {
        $validationContext = new ValidationResult();
        $validationContext->setError('username', 'Username not valid');
        $validationContext->clear();
        $this->assertNull($validationContext->getMessage());
        $this->assertEmpty($validationContext->getErrors());
    }
}
