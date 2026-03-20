<?php

namespace App\Tests\Unit;

use App\Entity\User;
use App\Security\Voter\UserVoter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[CoversClass(UserVoter::class)]
#[CoversClass(User::class)]
class UserVoterTest extends TestCase
{
    private UserVoter $voter;

    protected function setUp(): void
    {
        $this->voter = new UserVoter();
    }

    public function testSupportsAndNotSupports(): void
    {
        $user = new User();
        $token = $this->createMock(TokenInterface::class);

        // Reflection to test protected method supports
        $reflection = new \ReflectionClass(UserVoter::class);
        $method = $reflection->getMethod('supports');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->voter, UserVoter::EDIT, $user));
        $this->assertTrue($method->invoke($this->voter, UserVoter::TOGGLE_ACTIVE, $user));
        $this->assertTrue($method->invoke($this->voter, UserVoter::DELETE, $user));

        // Invalid attribute
        $this->assertFalse($method->invoke($this->voter, 'INVALID_ATTR', $user));

        // Invalid subject
        $this->assertFalse($method->invoke($this->voter, UserVoter::EDIT, new \stdClass()));
    }

    public function testAnonymousUserCannotDoAnything(): void
    {
        $targetUser = new User();

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        // test via public method vote()
        $this->assertEquals(
            UserVoter::ACCESS_DENIED,
            $this->voter->vote($token, $targetUser, [UserVoter::EDIT])
        );
    }

    public function testAdminCanEditAnyUser(): void
    {
        $targetUser = new User();

        $loggedUser = new User();
        $loggedUser->setRoles(['ROLE_ADMIN']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($loggedUser);

        $this->assertEquals(
            UserVoter::ACCESS_GRANTED,
            $this->voter->vote($token, $targetUser, [UserVoter::EDIT])
        );

        $this->assertEquals(
            UserVoter::ACCESS_GRANTED,
            $this->voter->vote($token, $targetUser, [UserVoter::TOGGLE_ACTIVE])
        );
    }

    public function testCannotDeleteAdminUser(): void
    {
        $adminTargetUser = new User();
        $adminTargetUser->setRoles(['ROLE_ADMIN']);

        $loggedUser = new User();
        $loggedUser->setRoles(['ROLE_ADMIN']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($loggedUser);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Cannot delete an admin user.');

        // Voter will throw exception directly inside voteOnAttribute
        // Note: Security component catches it or let it propagate depending on config, but our unit test will see it.
        $this->voter->vote($token, $adminTargetUser, [UserVoter::DELETE]);
    }

    public function testCanDeleteNormalUser(): void
    {
        $normalTargetUser = new User();
        $normalTargetUser->setRoles(['ROLE_USER']);

        $loggedUser = new User();
        $loggedUser->setRoles(['ROLE_ADMIN']);

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($loggedUser);

        $this->assertEquals(
            UserVoter::ACCESS_GRANTED,
            $this->voter->vote($token, $normalTargetUser, [UserVoter::DELETE])
        );
    }
}
