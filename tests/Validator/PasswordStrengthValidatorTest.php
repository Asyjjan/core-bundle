<?php

declare(strict_types=1);

namespace Leapt\CoreBundle\Tests\Validator;

use Leapt\CoreBundle\Validator\Constraints\PasswordStrength;
use Leapt\CoreBundle\Validator\Constraints\PasswordStrengthValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;

final class PasswordStrengthValidatorTest extends TestCase
{
    private ExecutionContext|MockObject|null $context;
    private ?PasswordStrengthValidator $validator;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(ExecutionContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->validator = new PasswordStrengthValidator();
        $this->validator->initialize($this->context);
    }

    protected function tearDown(): void
    {
        $this->context = null;
        $this->validator = null;
    }

    public function testNullIsValid(): void
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate(null, new PasswordStrength());
    }

    public function testEmptyStringIsValid(): void
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate('', new PasswordStrength());
    }

    /**
     * @dataProvider getValidPasswords
     */
    public function testValidPassword(string $password): void
    {
        $this->context->expects($this->never())
            ->method('addViolation');

        $this->validator->validate($password, new PasswordStrength(['score' => 50, 'min' => 5, 'max' => 255]));
    }

    public function getValidPasswords(): iterable
    {
        return [
            ['dora1*'],
            ['dora12*'],
            ['Dora12*'],
            ['E=mc^2'],
            ['Doraa12*'],
            ['Dor+a12*'],
            ['DorH+a12*5'],
            ['DorH+a12*5'],
            ['My password is f*cking awesome'],
        ];
    }

    /**
     * @dataProvider getInvalidPasswords
     */
    public function testInvalidPasswords(string $password): void
    {
        $constraint = new PasswordStrength([
            'scoreMessage' => 'scoreMessage',
            'score'        => 50,
        ]);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('scoreMessage');

        $this->validator->validate($password, $constraint);
    }

    public function getInvalidPasswords(): iterable
    {
        return [
            ['toto'],
            ['dora'],
            ['Dora'],
        ];
    }

    public function testMinPasswords(): void
    {
        $constraint = new PasswordStrength([
            'minMessage' => 'minMessage',
            'score'      => 50,
            'min'        => 5,
        ]);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('minMessage');

        $this->validator->validate('abc', $constraint);
    }

    public function testMaxPasswords(): void
    {
        $constraint = new PasswordStrength([
            'maxMessage' => 'maxMessage',
            'score'      => 50,
            'max'        => 5,
        ]);

        $this->context->expects($this->once())
            ->method('addViolation')
            ->with('maxMessage');

        $this->validator->validate('abcdefgh', $constraint);
    }
}
