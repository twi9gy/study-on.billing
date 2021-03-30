<?php

namespace App\Model;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="User Dto",
 *     description="User Dto"
 * )
 *
 * Class UserDto
 * @package App\Model
 */
class UserDto
{
    /**
     * @OA\Property(
     *     format="email",
     *     title="Email",
     *     description="Email",
     *     example="testUser@gmail.ru"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    /**
     * @OA\Property(
     *     format="string",
     *     title="Password",
     *     description="Password",
     *     example="123456"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     * @Assert\Length(min=6)
     */
    public $password;
}