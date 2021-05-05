<?php

namespace App\Model;

use JMS\Serializer\Annotation as Serialization;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Course Dto",
 *     description="Course Dto"
 * )
 * Class CourseDto
 * @package App\Model
 */
class CourseDto
{
    /**
     * @OA\Property(
     *     format="string",
     *     title="type",
     *     description="тип курса",
     *     example="rent|free|buy"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     */
    public $type;

    /**
     * @OA\Property(
     *     format="string",
     *     title="title",
     *     description="наименование курса",
     *     example="Интернет-маркетолог с нуля."
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     */
    public $title;

    /**
     * @OA\Property(
     *     format="string",
     *     title="code",
     *     description="символьный код курса",
     *     example="Internet-Marketer"
     * )
     *
     * @Serialization\Type("string")
     * @Assert\NotBlank()
     */
    public $code;

    /**
     * @OA\Property(
     *     format="float",
     *     title="price",
     *     description="стоимость курса",
     *     example="100"
     * )
     *
     * @Serialization\Type("float")
     * @Assert\Type("float")
     */
    public $price;
}